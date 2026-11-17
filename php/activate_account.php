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
$password  = $_POST['password'] ?? '';

if (empty($token) || empty($birthdate) || empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit();
}

try {
    // Verify token exists in patient_auth and matches the patient's birthdate in patient_records
    $stmt = $conn->prepare("
        SELECT pa.patient_id, pr.email, pa.password_hash 
        FROM patient_auth pa 
        JOIN patient_records pr ON pr.patient_id = pa.patient_id 
        WHERE pa.account_token = ? AND pr.date_of_birth = ? 
        LIMIT 1
    ");
    $stmt->bind_param("ss", $token, $birthdate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid Account Token or Date of Birth.');
    }

    $patient = $result->fetch_assoc();
    $patient_id = $patient['patient_id'];
    $email = $patient['email'];
    $existingPasswordHash = $patient['password_hash'];
    $stmt->close();

    // Check if the account has already been activated 
    if (!empty($existingPasswordHash)) {
        throw new Exception('This account has already been activated. Please log in.');
    }

    // Check if the requested username is already taken in patient_auth or admin_auth
    $userCheck = $conn->prepare("SELECT patient_auth_id FROM patient_auth WHERE username = ? LIMIT 1");
    $userCheck->bind_param("s", $username);
    $userCheck->execute();
    if ($userCheck->get_result()->num_rows > 0) {
        throw new Exception('Username is already taken.');
    }
    $userCheck->close();

    $adminCheck = $conn->prepare("SELECT 1 FROM admin_auth WHERE username = ? LIMIT 1");
    if ($adminCheck) {
        $adminCheck->bind_param("s", $username);
        $adminCheck->execute();
        if ($adminCheck->get_result()->num_rows > 0) {
            throw new Exception('Username is already taken.');
        }
        $adminCheck->close();
    }

    $options = ["cost" => 12];
    $hashed_password = password_hash($password, PASSWORD_BCRYPT, $options);
    $status = 'Offline';
    $role = 'Patient';

    // Update the existing patient_auth record with the new username, password, and status
    $updateAuth = $conn->prepare("
        UPDATE patient_auth 
        SET username = ?, password_hash = ?, status = ?, role = ? 
        WHERE patient_id = ? AND account_token = ?
    ");
    $updateAuth->bind_param("ssssis", $username, $hashed_password, $status, $role, $patient_id, $token);

    if (!$updateAuth->execute()) {
        throw new Exception('Failed to create login credentials: ' . $updateAuth->error);
    }
    $updateAuth->close();

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