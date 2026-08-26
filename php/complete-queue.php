<?php
// php/complete_queue.php
header('Content-Type: application/json');
require_once '../php/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$queue_info_id = isset($_POST['queue_info_id']) ? intval($_POST['queue_info_id']) : 0;
$patient_name    = isset($_POST['patient_name']) ? trim($_POST['patient_name']) : '';
$queue_code    = isset($_POST['queue_code']) ? trim($_POST['queue_code']) : '';
$service       = isset($_POST['service']) ? trim($_POST['service']) : '';
$accepted_at   = isset($_POST['accepted_at']) ? trim($_POST['accepted_at']) : '';


if ($queue_info_id <= 0 || trim($patient_name) === '') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters provided.']);
    exit;
}

// 1. CALCULATE SERVICE DURATION
$duration_str = "0 mins";
if (!empty($accepted_at)) {
    $start_time = new DateTime($accepted_at);
    $end_time   = new DateTime(); // Current system time
    $interval   = $start_time->diff($end_time);

    // Format human-readable string (e.g. "1 hr 15 mins" or "8 mins 30 secs")
    $parts = [];
    if ($interval->h > 0) $parts[] = $interval->h . ' hr' . ($interval->h > 1 ? 's' : '');
    if ($interval->i > 0) $parts[] = $interval->i . ' min' . ($interval->i > 1 ? 's' : '');
    if ($interval->s > 0 || empty($parts)) $parts[] = $interval->s . ' sec' . ($interval->s > 1 ? 's' : '');
    
    $duration_str = implode(' ', $parts);
}

// 2. BEGIN DATABASE TRANSACTION
$conn->begin_transaction();

try {
    // A. Insert into transactions table
    $insert_sql = "INSERT INTO transactions (patient_name, queue_code, service, service_duration) 
                  VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($insert_sql);
    $stmt_insert->bind_param("ssss", $patient_name, $queue_code, $service, $duration_str);
    
    if (!$stmt_insert->execute()) {
        throw new Exception("Insert transaction failed: " . $stmt_insert->error);
    }
    $stmt_insert->close();

    // B. Update status in queue_information to 'Completed'
    $update_sql = "UPDATE queue_information SET status = 'Completed' WHERE queue_info_id = ?";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("i", $queue_info_id);

    if (!$stmt_update->execute()) {
        throw new Exception("Update queue status failed: " . $stmt_update->error);
    }
    $stmt_update->close();

    // Commit both database actions
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction completed successfully.',
        'duration' => $duration_str
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>