<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$token        = trim($_POST['account_token'] ?? '');
$birthdate    = trim($_POST['birthdate'] ?? '');
$mothersName  = trim($_POST['mothers_name'] ?? '');
$newPassword  = trim($_POST['new_password'] ?? '');

if (empty($token) || empty($birthdate) || empty($mothersName) || empty($newPassword)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit();
}

if (strlen($newPassword) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters long.']);
    exit();
}

try {
    // 1. Verify token, birthdate, and mother's maiden name match across tables
    $sql = "
        SELECT pr.patient_id,
               pa.patient_auth_id 
        FROM patient_auth pa
        JOIN patient_records pr ON pa.patient_id = pr.patient_id
        WHERE pa.account_token = ? 
          AND pr.date_of_birth = ? 
          AND LOWER(pr.mothers_name) = LOWER(?) 
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("sss", $token, $birthdate, $mothersName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Verification failed. Account Token, Date of Birth, or Mother\'s Name is incorrect.');
    }

    $patient = $result->fetch_assoc();
    $patient_id = $patient['patient_id'];
    $patient_auth_id = $patient['patient_auth_id'];
    $stmt->close();

    // 2. Hash the new password and update patient_auth credentials
    $options = ["cost" => 12];
    $hashed_password = password_hash($newPassword, PASSWORD_BCRYPT, $options);

    $updateStmt = $conn->prepare("
        UPDATE patient_auth 
        SET password_hash = ?, status = 'Offline' 
        WHERE patient_id = ?
    ");
    if (!$updateStmt) {
        throw new Exception('Database prepare update failed: ' . $conn->error);
    }

    $updateStmt->bind_param("si", $hashed_password, $patient_id);
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update password. Please try again.');
    }
    $updateStmt->close();

    $logMessage = 'Patient reset password.';
    $userId = $patient_auth_id ?? null;
    $userType = 'Patient';

    $logStmt = $conn->prepare("INSERT INTO logs (user_id, user_type, log_message) VALUES (?, ?, ?)");
    if ($logStmt) {
        $logStmt->bind_param("iss", $userId, $userType, $logMessage);
        $logStmt->execute();
        $logStmt->close();
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Password reset successfully! You can now log in with your new password.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>