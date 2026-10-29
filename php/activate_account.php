<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$token     = trim($_POST['account_token'] ?? '');
$birthdate = trim($_POST['birthdate'] ?? '');
$username  = trim($_POST['username'] ?? '');
$password  = trim($_POST['password'] ?? '');

if (empty($token) || empty($birthdate) || empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit();
}

try {
    // 1. Verify patient record exists with this token and birthdate
    $stmt = $conn->prepare("SELECT patient_id, email FROM patient_records WHERE account_token = ? AND date_of_birth = ? LIMIT 1");
    $stmt->bind_param("ss", $token, $birthdate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid Account Token or Date of Birth.');
    }

    $patient = $result->fetch_assoc();
    $patient_id = $patient['patient_id'];
    $email = $patient['email'];
    $stmt->close();

    // 2. Check if auth account already exists
    $authCheck = $conn->prepare("SELECT patient_auth_id FROM patient_auth WHERE patient_id = ? LIMIT 1");
    $authCheck->bind_param("i", $patient_id);
    $authCheck->execute();
    if ($authCheck->get_result()->num_rows > 0) {
        throw new Exception('This account has already been activated. Please log in.');
    }
    $authCheck->close();

    $options = ["cost" => 12];
    $hashed_password = password_hash($password, PASSWORD_BCRYPT, $options);
    $status = 'Offline';
    $role = 'Patient';

    // 3. Insert into patient_auth
    $insertAuth = $conn->prepare("INSERT INTO patient_auth (patient_id, username, lastname, firstname, middlename, suffix, password_hash, email, account_token, status, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertAuth->bind_param("isiiiisisss", $patient_id, $username, $patient_id, $patient_id, $patient_id, $patient_id, $hashed_password, $patient_id, $token, $status, $role);

    if (!$insertAuth->execute()) {
        throw new Exception('Failed to create login credentials: ' . $insertAuth->error);
    }
    $insertAuth->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Account activated successfully! You can now log in.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>