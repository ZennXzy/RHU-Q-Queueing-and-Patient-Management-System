<?php
require_once '../api/check-private.php';
authorizeRoles(['Admin']);
require_once '../php/connection.php';
require_once '../php/reports_common.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new InvalidArgumentException('GET required');
    }

    $action = (string)($_GET['action'] ?? 'dashboard');

    if ($action === 'options') {
        // Fetch drop-down lookup choices
        $data = report_select_options($conn);
    } elseif ($action === 'details') {
        // Fetch single transaction details view
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            throw new InvalidArgumentException('Invalid transaction ID');
        }

        $rows = report_run($conn, "SELECT t.*, CONCAT_WS(' ', p.firstname, p.middlename, p.lastname, p.suffix) AS fullname, p.gender, p.date_of_birth, p.age, b.barangay_address AS address, COALESCE(NULLIF(t.service_snapshot, ''), s.service_name, 'Unknown service') AS service_type FROM transactions t JOIN patient_records p ON p.patient_id = t.patient_id LEFT JOIN barangay b ON b.barangay_id = p.address LEFT JOIN services s ON s.service_id = t.service WHERE t.transaction_id = ?", [$id], 'i');

        if (!$rows) {
            throw new InvalidArgumentException('Record not found');
        }

        $data = ['record' => $rows[0]];
    } else {
        $f = report_filters();

        if ($action === 'dashboard') {
            // Fetch analytical statistics and summary groups
            $data = report_summary($conn, $f);
        } elseif ($action === 'patients') {
            // Fetch paginated list data
            $page = max(1, min(100000, (int)($_GET['page'] ?? 1)));
            $perPage = 25;
            $params = [];
            $types = '';
            $where = report_where($f, $params, $types);

            $countRows = report_run($conn, 'SELECT COUNT(*) AS n ' . report_base() . ' WHERE ' . $where, $params, $types);
            $count = (int)$countRows[0]['n'];

            $data = [
                'rows' => report_rows($conn, $f, $perPage, ($page - 1) * $perPage),
                'total' => $count,
                'page' => $page,
                'per_page' => $perPage
            ];
        } else {
            throw new InvalidArgumentException('Unknown action');
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ], JSON_INVALID_UTF8_SUBSTITUTE);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (Throwable $e) {
    error_log('RHU reports: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to load reports. Check server logs.'
    ]);
}