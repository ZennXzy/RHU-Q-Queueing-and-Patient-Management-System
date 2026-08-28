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


if ($queue_info_id <= 0 || trim($patient_name) === '') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters provided.']);
    exit;
}

try {
    $service_time = NULL;
    $accepted_time = NULL;
    // B. Update status in queue_information to 'Completed'
    $update_sql = "UPDATE queue_information SET status = 'Completed', service_duration = NOW() WHERE queue_info_id = ?";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("i", $queue_info_id);

    if (!$stmt_update->execute()) {
        throw new Exception("Update queue status failed: " . $stmt_update->error);
    }
    $stmt_update->close();

    $getTime = "SELECT accepted_at, service_duration FROM queue_information WHERE queue_info_id = ?";

    $stmt_get = $conn->prepare($getTime);
    $stmt_get->bind_param("i", $queue_info_id);
    $stmt_get->execute();

    $result = $stmt_get->get_result();

    if ($row = $result->fetch_assoc()) {
        $accepted_time = $row['accepted_at'];
        $service_time = $row['service_duration'];
    }

    $start = new DateTime($accepted_time);
    $end = new DateTime($service_time);

    $interval = $start->diff($end);
    $totalSeconds = $end->getTimestamp() - $start->getTimestamp();
    $service_duration = $interval->format('%d days, %h hours, %i minutes'); 
    $stmt_get->close();

    $insert_sql = "INSERT INTO transactions (patient_name, queue_code, service, service_duration) 
                  VALUES (?, ?, ?, ?)";

    $stmt_insert = $conn->prepare($insert_sql);
    $stmt_insert->bind_param("ssss", $patient_name, $queue_code, $service, $service_duration);
    
    if (!$stmt_insert->execute()) {
        throw new Exception("Insert transaction failed: " . $stmt_insert->error);
    }
    $stmt_insert->close();

    // Commit both database actions
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction completed successfully.',
        'duration' => $totalSeconds
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