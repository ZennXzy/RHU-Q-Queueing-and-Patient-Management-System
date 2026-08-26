<?php
header('Content-Type: application/json');
require_once '../php/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$queue_info_id = isset($_POST['queue_info_id']) ? intval($_POST['queue_info_id']) : 0;

if ($queue_info_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Queue ID provided.']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE queue_information SET status = 'In Progress' WHERE queue_info_id = ?");
    $stmt->bind_param("i", $queue_info_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Patient status updated to In Progress.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Record not found or status already updated.'
            ]);
        }
    } else {
        throw new Exception($stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>