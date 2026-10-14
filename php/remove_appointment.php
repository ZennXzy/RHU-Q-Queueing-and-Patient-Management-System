<?php
session_start();
header('Content-Type: application/json');

require_once '../php/connection.php';
require_once '../api/check-private.php';
date_default_timezone_set('Asia/Manila');

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit();
}

// Get and sanitize appointment ID parameter
$appointments_id   = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;

if (!$appointments_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing Appointment ID.'
    ]);
    exit();
}

try {
    // 1. Verify that the appointment exists
    $checkStmt =$conn->prepare("SELECT appointments_id FROM appointments WHERE appointments_id = ? LIMIT 1");
    $checkStmt->bind_param("i", $appointments_id);$checkStmt->execute();
    $checkResult =$checkStmt->get_result();

    if ($checkResult->num_rows === 0) {$checkStmt->close();
        echo json_encode([
            'status' => 'error',
            'message' => 'Appointment record not found.'
        ]);
        exit();
    }
    $checkStmt->close();

    // 2. Perform the delete query
    $deleteStmt =$conn->prepare("DELETE FROM appointments WHERE appointments_id = ?");

    if (!$deleteStmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }

    $deleteStmt->bind_param("i", $appointments_id);

    if ($deleteStmt->execute()) {$deleteStmt->close();
        echo json_encode([
            'status' => 'success',
            'message' => 'Appointment removed successfully.'
        ]);
        $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
        if ($insertlog) {
            $message = $username . " added a queue from appointments.";
            $insertlog->bind_param('s', $message);
            $insertlog->execute();
            $insertlog->close();
        }
        exit();
    } else {
        throw new Exception("Failed to remove appointment: " . $deleteStmt->error);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
    exit();
} finally {
    $conn->close();
}
?>