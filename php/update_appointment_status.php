<?php
session_start();
// Prevent any HTML warnings/notices from corrupting the JSON output
ob_start();
header('Content-Type: application/json');

require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

// 1. Retrieve the username securely from session
$username = $_SESSION['username'] ?? $_SESSION['logged_user'] ?? $_SESSION['fullname'] ?? 'Staff/Doctor';

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
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
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing Appointment ID.'
    ]);
    exit();
}

if (!in_array($action, ['Approve', 'Decline'], true)) {
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid action type.'
    ]);
    exit();
}

if ($action === 'Decline' && empty($reason)) {
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'A reason is require_onced when declining an appointment.'
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
        ob_clean();
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

        // 2. Safely log the action
        $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
        if ($insertlog) {
            $logMsg = $username . " " . strtolower($action) . "d an appointment (ID: " . $appointmentId . ").";
            $insertlog->bind_param('s', $logMsg);
            $insertlog->execute();
            $insertlog->close();
        }

        ob_clean();
        echo json_encode([
            'status' => 'success',
            'message' => "Appointment successfully {$action}d."
        ]);
        exit();
    } else {
        throw new Exception("Failed to update status: " . $updateStmt->error);
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
    exit();
}
?>