<?php
header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';
date_default_timezone_set('Asia/Manila');

// Validate and sanitize incoming filter parameters
function report_filters(): array {
    $keys = ['service', 'barangay', 'gender', 'employment_status', 'civil_status', 'religion', 'phil_status', 'yakap_member', 'date_from', 'date_to', 'age_min', 'age_max', 'search', 'service_label'];
    $f = [];

    foreach ($keys as $key) {
        $f[$key] = trim((string)($_GET[$key] ?? ''));
    }

    // Validate integer inputs
    foreach (['service', 'barangay', 'age_min', 'age_max'] as $key) {
        if ($f[$key] !== '' && (!ctype_digit($f[$key]) || (int)$f[$key] > 100000)) {
            throw new InvalidArgumentException('Invalid ' . $key);
        }
    }

    foreach (['age_min', 'age_max'] as $key) {
        if ($f[$key] !== '' && (int)$f[$key] > 120) {
            throw new InvalidArgumentException('Invalid age');
        }
    }

    if ($f['age_min'] !== '' && $f['age_max'] !== '' && (int)$f['age_min'] > (int)$f['age_max']) {
        throw new InvalidArgumentException('Minimum age exceeds maximum age');
    }

    // Validate dates format and range
    foreach (['date_from', 'date_to'] as $key) {
        if ($f[$key] !== '') {
            $date = DateTime::createFromFormat('!Y-m-d', $f[$key]);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $f[$key]) || !$date || $date->format('Y-m-d') !== $f[$key]) {
                throw new InvalidArgumentException('Invalid date');
            }
        }
    }

    if ($f['date_from'] !== '' && $f['date_to'] !== '' && $f['date_from'] > $f['date_to']) {
        throw new InvalidArgumentException('Start date exceeds end date');
    }

    if (strlen($f['search']) > 120) {
        throw new InvalidArgumentException('Search is too long');
    }

    return $f;
}

// Build dynamic WHERE clause based on filters
function report_where(array $f, array &$params, string &$types): string {
    $w = ['1=1'];

    if ($f['service_label'] !== '') {
        $w[] = "COALESCE(NULLIF(t.service_snapshot, ''), s.service_name, 'Unknown service') = ?";
        $params[] = $f['service_label'];
        $types .= 's';
    }

    foreach (['service' => 't.service', 'barangay' => 'p.address'] as $key => $column) {
        if ($f[$key] !== '') {
            $w[] = "$column = ?";
            $params[] = (int)$f[$key];
            $types .= 'i';
        }
    }

    foreach (['gender' => 'p.gender', 'employment_status' => 'p.employment_status', 'civil_status' => 'p.civil_status', 'religion' => 'p.religion', 'phil_status' => 'p.phil_status', 'yakap_member' => 'p.yakap_member'] as $key => $column) {
        if ($f[$key] !== '') {
            $w[] = "$column = ?";
            $params[] = $f[$key];
            $types .= 's';
        }
    }

    if ($f['date_from'] !== '') {
        $w[] = 't.transaction_date >= ?';
        $params[] = $f['date_from'] . ' 00:00:00';
        $types .= 's';
    }

    if ($f['date_to'] !== '') {
        $w[] = 't.transaction_date < DATE_ADD(?, INTERVAL 1 DAY)';
        $params[] = $f['date_to'];
        $types .= 's';
    }

    // Dynamic age calculation against transaction timestamp
    $age = 'TIMESTAMPDIFF(YEAR, p.date_of_birth, DATE(t.transaction_date))';
    if ($f['age_min'] !== '') {
        $w[] = "$age >= ?";
        $params[] = (int)$f['age_min'];
        $types .= 'i';
    }

    if ($f['age_max'] !== '') {
        $w[] = "$age <= ?";
        $params[] = (int)$f['age_max'];
        $types .= 'i';
    }

    if ($f['search'] !== '') {
        $w[] = "(CONCAT_WS(' ', p.firstname, p.lastname, p.middlename) LIKE ? OR t.queue_code LIKE ? OR CAST(p.patient_id AS CHAR) LIKE ?)";
        $q = '%' . $f['search'] . '%';
        array_push($params, $q, $q, $q);
        $types .= 'sss';
    }

    return implode(' AND ', $w);
}

// Reusable SQL base join structure
function report_base(): string {
    return 'FROM transactions t JOIN patient_records p ON p.patient_id = t.patient_id LEFT JOIN barangay b ON b.barangay_id = p.address LEFT JOIN services s ON s.service_id = t.service';
}

// Execute parameterized query safely
function report_run(mysqli $conn, string $sql, array $params = [], string $types = ''): array {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Database query preparation failed');
    }

    if ($params) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new RuntimeException('Database query execution failed');
    }

    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// Fetch filter dropdown options
function report_select_options(mysqli $conn): array {
    $data = [
        'services' => report_run($conn, 'SELECT service_id AS id, service_name AS name FROM services ORDER BY service_name'),
        'barangays' => report_run($conn, 'SELECT barangay_id AS id, barangay_address AS name FROM barangay ORDER BY barangay_address')
    ];

    foreach (['gender', 'employment_status', 'civil_status', 'religion', 'phil_status', 'yakap_member'] as $column) {
        $rows = report_run($conn, "SELECT DISTINCT `$column` AS value FROM patient_records WHERE `$column` IS NOT NULL AND `$column` <> '' ORDER BY `$column`");
        $data[$column] = array_column($rows, 'value');
    }

    return $data;
}

// Fetch paginated transaction report rows
function report_rows(mysqli $conn, array $f, int $limit = 25, int $offset = 0): array {
    $params = [];
    $types = '';
    $where = report_where($f, $params, $types);

    $sql = "SELECT t.transaction_id, t.patient_id, t.queue_code, t.transaction_date, CONCAT_WS(' ', p.firstname, p.middlename, p.lastname, p.suffix) AS patient_name, TIMESTAMPDIFF(YEAR, p.date_of_birth, DATE(t.transaction_date)) AS age_at_visit, p.gender, p.employment_status, p.civil_status, p.phil_status, p.yakap_member, COALESCE(b.barangay_address, 'Unspecified') AS barangay, COALESCE(NULLIF(t.service_snapshot, ''), s.service_name, 'Unknown service') AS service_name " . report_base() . " WHERE $where ORDER BY t.transaction_date DESC, t.transaction_id DESC";

    if ($limit > 0) {
        $sql .= ' LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
    }

    return report_run($conn, $sql, $params, $types);
}

// Aggregate analytical metrics for dashboard view
function report_summary(mysqli $conn, array $f): array {
    $params = [];
    $types = '';
    $where = report_where($f, $params, $types);
    $base = report_base();

    $kpi = report_run($conn, "SELECT COUNT(*) AS visits, COUNT(DISTINCT t.patient_id) AS patients, COUNT(DISTINCT p.address) AS barangays FROM transactions t JOIN patient_records p ON p.patient_id = t.patient_id LEFT JOIN services s ON s.service_id = t.service WHERE $where", $params, $types)[0];

    $groupings = [
        'services' => "COALESCE(NULLIF(t.service_snapshot, ''), s.service_name, 'Unknown service')",
        'barangays' => "COALESCE(b.barangay_address, 'Unspecified')",
        'gender' => "COALESCE(NULLIF(p.gender, ''), 'Unspecified')",
        'employment' => "COALESCE(NULLIF(p.employment_status, ''), 'Unspecified')",
        'civil' => "COALESCE(NULLIF(p.civil_status, ''), 'Unspecified')",
        'philhealth' => "COALESCE(NULLIF(p.phil_status, ''), 'Unspecified')",
        'yakap' => "COALESCE(NULLIF(p.yakap_member, ''), 'Unspecified')",
        'age' => "CASE WHEN TIMESTAMPDIFF(YEAR, p.date_of_birth, DATE(t.transaction_date)) < 5 THEN '0–4' WHEN TIMESTAMPDIFF(YEAR, p.date_of_birth, DATE(t.transaction_date)) < 18 THEN '5–17' WHEN TIMESTAMPDIFF(YEAR, p.date_of_birth, DATE(t.transaction_date)) < 60 THEN '18–59' ELSE '60+' END",
        'months' => "DATE_FORMAT(t.transaction_date, '%Y-%m')"
    ];

    $groups = [];
    foreach ($groupings as $key => $expr) {
        $groups[$key] = report_run($conn, "SELECT $expr AS label, COUNT(*) AS visits, COUNT(DISTINCT t.patient_id) AS patients $base WHERE $where GROUP BY label ORDER BY visits DESC, label ASC LIMIT 60", $params, $types);
    }

    return ['kpi' => $kpi, 'groups' => $groups];
}