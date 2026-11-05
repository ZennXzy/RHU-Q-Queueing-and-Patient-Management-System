<?php
header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';
date_default_timezone_set('Asia/Manila');

if (!canManageRecords()) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Unauthorized action. Admin privileges missing.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit();
}

$queue_info_id = isset($_POST['queue_info_id']) ? intval($_POST['queue_info_id']) : 0;

if ($queue_info_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing Queue ID.'
    ]);
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM queue_information WHERE queue_info_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }

    $stmt->bind_param("i", $queue_info_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Queue entry deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Queue record not found or already deleted.'
            ]);
        }
    } else {
        throw new Exception("Execution failed: " . $stmt->error);
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