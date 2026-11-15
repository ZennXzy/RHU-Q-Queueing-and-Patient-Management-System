<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$queue_info_id = (int)($_POST['queue_info_id'] ?? 0);
$patient_name = trim($_POST['patient_name'] ?? '');
$queue_code = trim($_POST['queue_code'] ?? '');
$service = trim($_POST['service'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');
$appointment = (int)($_POST['add_appointment'] ?? 0);
$appointment_date = trim($_POST['appointment_date'] ?? '');
$server_name = $_SESSION['logged_id'] ?? '';

if ($queue_info_id <= 0 || $patient_name === '' || $service === '') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters provided.']);
    exit;
}

try {
    $conn->begin_transaction();

    // 1. Fetch queue record and verify status
    $stmt = $conn->prepare("SELECT * FROM queue_information WHERE queue_info_id = ? FOR UPDATE");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("i", $queue_info_id);
    $stmt->execute();
    $queue = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$queue) throw new Exception('Queue record not found.');
    if ($queue['status'] === 'Completed') throw new Exception('This queue has already been completed.');
    if ($queue['status'] !== 'In Progress') throw new Exception("Cannot complete queue. Current status is '{$queue['status']}' instead of 'In Progress'.");

    $patient_id = (int)$queue['patient_id'];
    $queue_code = $queue['queue_code'];
    $queue_service = trim((string)$queue['service_snapshot']);
    $accepted_at = $queue['accepted_at'];

    // 2. CREATE CURRENT TIME VARIABLE & CALCULATE DURATION
    $currentTime = date('Y-m-d H:i:s'); // Current time variable
    
    $start = new DateTime($accepted_at ?: $currentTime);
    $end = new DateTime($currentTime);
    
    // Total elapsed seconds (ideal if your service_duration column is INT)
    $totalSeconds = max(0, $end->getTimestamp() - $start->getTimestamp());

    // Optional formatted string (if your column is VARCHAR)
    $interval = $start->diff($end);
    $service_duration_str = ($interval->days > 0 ? $interval->format('%a days, ') : '') . $interval->format('%h hours, %i minutes, %s seconds');

    // 3. Update Queue Information using our $currentTime variable
    $stmt = $conn->prepare("UPDATE queue_information SET status = 'Completed', service_duration = ? WHERE queue_info_id = ?");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("si", $service_duration_str, $queue_info_id); // or bind $totalSeconds if column is INT

    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        throw new Exception('Failed to update queue completion status.');
    }
    $stmt->close();

    // Get patient contact & service ID for transaction logging
    $stmt = $conn->prepare("SELECT contact_number FROM patient_records WHERE patient_id = ? LIMIT 1");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $patientRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $patient_contact = $patientRow['contact_number'] ?? null;

    $stmt = $conn->prepare("SELECT service_id FROM services WHERE service_name = ? LIMIT 1");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("s", $service);
    $stmt->execute();
    $serviceRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$serviceRow) throw new Exception('Please select a valid service.');
    $service_id = (int)$serviceRow['service_id'];

    // 4. Insert into Transactions
    $insertSql = "INSERT INTO transactions
        (patient_id, queue_code, service, service_snapshot, blood_pressure, respiratory_rate, temperature, pulse_rate, weight, height, remarks, diagnosis, server_name, service_duration, transaction_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($insertSql);
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("isissididdsssi", 
        $patient_id, $queue_code, $service_id, $service, 
        $queue['blood_pressure'], $queue['respiratory_rate'], $queue['temperature'], $queue['pulse_rate'], 
        $queue['weight'], $queue['height'], $remarks, $queue['diagnosis'], $server_name, $service_duration_str
    );

    if (!$stmt->execute()) throw new Exception('Failed to save completed transaction.');
    $stmt->close();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction completed successfully.',
        'duration_seconds' => $totalSeconds
    ]);

} catch (Throwable $e) {
    $conn->rollback();
    error_log('Complete queue error: ' . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>