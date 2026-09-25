<?php
session_start();
header('Content-Type: application/json');

require_once '../php/connection.php';

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit();
}

// Extract and sanitize input parameters
$appointmentId = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
$action        = trim($_POST['action'] ?? '');
$reason        = trim($_POST['reason'] ?? '');

// Validation checks
if (!$appointmentId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing Appointment ID.'
    ]);
    exit();
}

if (!in_array($action, ['Approve', 'Decline'], true)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid action type.'
    ]);
    exit();
}

if ($action === 'Decline' && empty($reason)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'A reason is required when declining an appointment.'
    ]);
    exit();
}

// Map action to database status values
$newStatus = ($action === 'Approve') ? 'Approved' : 'Declined';
$declineReason = ($action === 'Decline') ? $reason : NULL;

try {
    // Check if appointment exists before updating
    $checkStmt = $conn->prepare("SELECT appointments_id FROM appointments WHERE appointments_id = ? LIMIT 1");
    $checkStmt->bind_param("i", $appointmentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        $checkStmt->close();
        echo json_encode([
            'status' => 'error',
            'message' => 'Appointment record not found.'
        ]);
        exit();
    }
    $checkStmt->close();

    // Prepare update query
    $updateStmt = $conn->prepare("
        UPDATE appointments 
        SET status = ?, 
            note = ?, 
            updated_at = NOW() 
        WHERE appointments_id = ?
    ");

    if (!$updateStmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }

    $updateStmt->bind_param("ssi", $newStatus, $declineReason, $appointmentId);

    if ($updateStmt->execute()) {
        $updateStmt->close();
        echo json_encode([
            'status' => 'success',
            'message' => "Appointment successfully {$action}d."
        ]);
        $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
        if ($newStatus === "Approved"){
          if ($insertlog) {
              $message = $username . " approved an appointment.";
              $insertlog->bind_param('s', $message);
              $insertlog->execute();
          }
        } else {
              $message = $username . " declined an appointment.";
              $insertlog->bind_param('s', $message);
              $insertlog->execute();
        }
        $insertlog->close();
        exit();
    } else {
        throw new Exception("Failed to update status: " . $updateStmt->error);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
    exit();
}
?>