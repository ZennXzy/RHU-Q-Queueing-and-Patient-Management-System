<?php
session_start();
header('Content-Type: application/json');

// 1. Include system connection and authorization guards
require_once '../php/connection.php';
require_once '../api/check-private.php';
date_default_timezone_set('Asia/Manila');

// Authorize patient role
authorizeRoles(['Patient']);

// 2. Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit();
}

// 3. Extract and sanitize inputs
$patient_id       = $_SESSION['patient_id'] ?? 0;
$service          = isset($_POST['service']) ? trim($_POST['service']) : (isset($_POST['service_name']) ? trim($_POST['service_name']) : '');
$appointment_date = isset($_POST['appointment_date']) ? trim($_POST['appointment_date']) : '';
$remarks          = isset($_POST['remarks']) ? trim($_POST['remarks']) : (isset($_POST['reason']) ? trim($_POST['reason']) : '');

// 4. Validate require_onced fields
if ($patient_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized or invalid session patient account.'
    ]);
    exit();
}

if (empty($service)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please select a valid health service.'
    ]);
    exit();
}

if (empty($appointment_date)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please select a preferred appointment date.'
    ]);
    exit();
}

// Ensure the appointment date is not in the past
if (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Appointment date cannot be in the past.'
    ]);
    exit();
}

try {
    // 5. Insert new appointment record into database with 'Pending' status
    $sql = "INSERT INTO appointments (patient_id, service, note, appointment_date, status, created_at) 
            VALUES (?, ?, ?, ?, 'Pending', NOW())";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    $stmt->bind_param("isss", $patient_id, $service, $remarks, $appointment_date);

    if ($stmt->execute()) {
        $new_appointment_id = $stmt->insert_id;

            
        $logMessage = "Added an Appointment";
        $userId = $_SESSION['logged_id'] ?? null;
        $userType = $_SESSION['logged_role'] ?? 'Staff';

        $stmt = $conn->prepare("INSERT INTO logs (user_id, user_type, log_message) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iss", $userId, $userType, $logMessage);
            $stmt->execute();
            $stmt->close();
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Appointment request submitted successfully!',
            'appointment_id' => $new_appointment_id
        ]);
    } else {
        throw new Exception("Execution error: " . $stmt->error);
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