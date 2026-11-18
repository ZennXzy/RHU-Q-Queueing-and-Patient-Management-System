<?php
// Secure private route check and role authorization
require_once '../api/check-private.php';
authorizeRoles(['Admin']);

require_once '../php/connection.php';
require_once '../php/reports_common.php';

// Prevent CSV formula injection vulnerabilities
function csv_safe($value): string {
    $s = (string)($value ?? '');
    if (preg_match('/^[\x00-\x20]*[=+@\-\t\r]/u', $s)) {
        $s = "'" . $s;
    }
    return $s;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new InvalidArgumentException('GET required');
    }

    $f = report_filters();
    $mode = (string)($_GET['mode'] ?? 'visits');

    if (!in_array($mode, ['visits', 'patients', 'summary'], true)) {
        throw new InvalidArgumentException('Invalid export type');
    }

    $summary = null;
    $stmt = null;
    $result = null;

    if ($mode === 'summary') {
        $summary = report_summary($conn, $f);
    } else {
        $params = [];
        $types = '';
        $where = report_where($f, $params, $types);

        if ($mode === 'patients') {
            $sql = "SELECT p.patient_id, CONCAT_WS(' ', p.firstname, p.middlename, p.lastname, p.suffix) AS patient_name, COALESCE(b.barangay_name, 'Unspecified') AS barangay, p.gender, p.employment_status, p.civil_status, p.phil_status, p.yakap_member, COUNT(*) AS service_visits, MAX(t.transaction_date) AS last_visit " . report_base() . " WHERE $where GROUP BY p.patient_id, p.firstname, p.middlename, p.lastname, p.suffix, b.barangay_name, p.gender, p.employment_status, p.civil_status, p.phil_status, p.yakap_member ORDER BY last_visit DESC";
        } else {
            $sql = "SELECT t.transaction_id, t.patient_id, CONCAT_WS(' ', p.firstname, p.middlename, p.lastname, p.suffix) AS patient_name, t.queue_code, COALESCE(NULLIF(t.service_snapshot, ''), s.service_name, 'Unknown service') AS service_name, COALESCE(b.barangay_name, 'Unspecified') AS barangay, TIMESTAMPDIFF(YEAR, p.date_of_birth, DATE(t.transaction_date)) AS age_at_visit, p.gender, p.employment_status, p.civil_status, p.phil_status, p.yakap_member, t.transaction_date " . report_base() . " WHERE $where ORDER BY t.transaction_date DESC, t.transaction_id DESC";
        }

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Export query failed: ' . $conn->error);
        }

        if ($params) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new RuntimeException('Export execution failed: ' . $stmt->error);
        }

        $result = $stmt->get_result();
    }
    
    $logMessage = 'Admin exported RHU reports CSV (' . $mode . ')';
    $userId = $_SESSION['logged_id'] ?? null;
    $userType = $_SESSION['logged_role'] ?? 'Staff';

    $logstmt = $conn->prepare("INSERT INTO logs (user_id, user_type, log_message) VALUES (?, ?, ?)");
    if ($logstmt) {
        $logstmt->bind_param("iss", $userId, $userType, $logMessage);
        $logstmt->execute();
        $logstmt->close();
    }

    $filename = 'rhuq_' . $mode . '_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");

    if ($mode === 'summary') {
        fputcsv($out, ['Category', 'Label', 'Service Visits', 'Unique Patients']);
        foreach ($summary['groups'] as $category => $rows) {
            foreach ($rows as $row) {
                fputcsv($out, array_map('csv_safe', [$category, $row['label'], $row['visits'], $row['patients']]));
            }
        }
    } else {
        if ($mode === 'patients') {
            fputcsv($out, ['Patient ID', 'Patient Name', 'Barangay', 'Gender', 'Employment', 'Civil Status', 'PhilHealth', 'Yakap', 'Service Visits', 'Last Visit']);
        } else {
            fputcsv($out, ['Transaction ID', 'Patient ID', 'Patient Name', 'Queue Code', 'Service', 'Barangay', 'Age at Visit', 'Gender', 'Employment', 'Civil Status', 'PhilHealth', 'Yakap', 'Transaction Date']);
        }

        while ($row = $result->fetch_assoc()) {
            fputcsv($out, array_map('csv_safe', array_values($row)));
        }
        $stmt->close();
    }

    fclose($out);

} catch (Throwable $e) {
    error_log('RHU export: ' . $e->getMessage());
    if (!headers_sent()) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Export failed: ' . $e->getMessage();
}