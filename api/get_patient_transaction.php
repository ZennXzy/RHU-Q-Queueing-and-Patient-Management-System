<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$patientId = NULL;

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    $patientId = isset($_REQUEST['patient_id']) ? intval($_REQUEST['patient_id']) : 0; 
} else {
    // Fallback across potential session variable keys
    $patientId = $_SESSION['patient_id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
}

if (!$patientId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid Patient ID or missing session.'
    ]);
    exit();
}

try {
    $query = "
        SELECT 
            t.transaction_id,
            t.queue_code,
            s.service_name AS service,
            COALESCE(t.remarks, 'None') AS remarks,
            COALESCE(a.fullname, 'N/A') AS server_name,
            COALESCE(t.service_duration, 'N/A') AS service_duration,
            DATE_FORMAT(t.transaction_date, '%b %d, %Y %h:%i %p') AS transaction_date
        FROM transactions t
        LEFT JOIN admin_auth a ON t.server_name = a.id
        LEFT JOIN services s ON t.service = s.service_id
        WHERE t.patient_id = ?
        ORDER BY t.transaction_date DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    $result = $stmt->get_result();

    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = [
            'transaction_id'   => $row['transaction_id'] ?? null,
            'queue_code'       => htmlspecialchars($row['queue_code']),
            'service'          => htmlspecialchars($row['service']),
            'remarks'          => htmlspecialchars($row['remarks']),
            'server_name'      => htmlspecialchars($row['server_name']),
            'service_duration' => htmlspecialchars($row['service_duration']),
            'transaction_date' => $row['transaction_date']
        ];
    }
    $stmt->close();

    echo json_encode([
        'status'       => 'success',
        'data'         => $transactions,
        'transactions' => $transactions
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>