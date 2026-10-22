<?php
require_once '../api/check-private.php';
require_once '../php/connection.php';

// Enforce Patient Authorization
authorizeRoles(['Patient']);

$patient_id = $_SESSION['patient_id'] ?? null;
header('Content-Type: application/json');

if (!$patient_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized patient session.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $queue_id = $_POST['queue_id'] ?? null;

    if (!$queue_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid queue ID specified.']);
        exit();
    }

    // Securely delete only if the queue belongs to the patient and status is Waiting
    $stmt = $conn->prepare("DELETE FROM queue_information WHERE queue_code = ? AND patient_id = ? AND status = 'Waiting'");
    
    if ($stmt) {
        $stmt->bind_param("ii", $queue_id, $patient_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Queue request cancelled successfully.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to cancel queue. It may already be processing or completed.'
            ]);
        }
        $stmt->close();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database query preparation failed.'
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}