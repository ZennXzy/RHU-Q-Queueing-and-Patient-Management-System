<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../php/connection.php';
require_once '../api/check-private.php';

// Validate Request
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Get Patient ID
$patientId = null;

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    $patientId = isset($_REQUEST['patient_id']) ? (int)$_REQUEST['patient_id'] : 0;
} else {
    $patientId = $_SESSION['patient_id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
}

$patientId = (int)$patientId;

if ($patientId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Patient ID or missing session.']);
    exit;
}

// Format Service Duration
function formatServiceDuration($seconds) {
    if ($seconds === null || !is_numeric($seconds)) {
        return 'N/A';
    }

    $seconds = max(0, (int)$seconds);
    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $remainingSeconds = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
}

try {
    // Fetch Transactions
    $query = "
        SELECT
            t.transaction_id,
            t.queue_code,
            t.service_snapshot,
            s.service_name AS service,
            COALESCE(t.remarks, 'None') AS remarks,
            COALESCE(a.fullname, 'N/A') AS staff_id,
            t.service_duration,
            DATE_FORMAT(t.transaction_date, '%b %d, %Y %h:%i %p') AS transaction_date
        FROM transactions t
        LEFT JOIN admin_auth a ON t.staff_id = a.id
        LEFT JOIN services s ON t.service_id = s.service_id
        WHERE t.patient_id = ?
        ORDER BY t.transaction_date DESC
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = [];

    // Prepare Transaction Records
    while ($row = $result->fetch_assoc()) {
        $durationSeconds = $row['service_duration'];

        $transactions[] = [
            'transaction_id' => $row['transaction_id'] ?? null,
            'queue_code' => (string)($row['queue_code'] ?? ''),
            'service' => !empty($row['service']) ? $row['service'] : ($row['service_snapshot'] ?? 'N/A'),
            'remarks' => (string)($row['remarks'] ?? 'None'),
            'staff_id' => (string)($row['staff_id'] ?? 'N/A'),
            'staff_name' => (string)($row['staff_id'] ?? 'N/A'),
            'service_duration' => $durationSeconds !== null ? (int)$durationSeconds : null,
            'service_duration_formatted' => formatServiceDuration($durationSeconds),
            'transaction_date' => $row['transaction_date'] ?? null
        ];
    }

    $stmt->close();

    // JSON Response
    echo json_encode([
        'status' => 'success',
        'data' => $transactions,
        'transactions' => $transactions
    ]);

} catch (Throwable $e) {
    error_log('Patient transaction error: ' . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to retrieve transaction records.'
    ]);
}

$conn->close();
?>