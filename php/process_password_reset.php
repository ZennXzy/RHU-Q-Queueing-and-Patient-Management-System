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
    // 1. Verify token, birthdate, and mother's maiden name match (case-insensitive check for mother's name)
    $stmt = $conn->prepare("
        SELECT patient_id 
        FROM patient_records 
        WHERE account_token = ? 
          AND date_of_birth = ? 
          AND LOWER(mothers_name) = LOWER(?) 
        LIMIT 1
    ");
    $stmt->bind_param("sss", $token, $birthdate, $mothersName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Verification failed. Account Token, Date of Birth, or Mother\'s Name is incorrect.');
    }

    $patient = $result->fetch_assoc();
    $patient_id = $patient['patient_id'];
    $stmt->close();

    // 2. Check if an auth account exists for this patient
    $authCheck = $conn->prepare("SELECT patient_auth_id FROM patient_auth WHERE patient_id = ? LIMIT 1");
    $authCheck->bind_param("i", $patient_id);
    $authCheck->execute();
    $authRes = $authCheck->get_result();

    if ($authRes->num_rows === 0) {
        throw new Exception('No online account is linked to this record yet. Please activate your account first.');
    }
    $authCheck->close();

    // 3. Hash the new password and update patient_auth
    $options = ["cost" => 12];
    $hashed_password = password_hash($newPassword, PASSWORD_BCRYPT, $options);

    $updateStmt = $conn->prepare("UPDATE patient_auth SET password_hash = ?, status = 'Offline' WHERE patient_id = ?");
    $updateStmt->bind_param("si", $hashed_password, $patient_id);

    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update password. Please try again.');
    }
    $updateStmt->close();

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