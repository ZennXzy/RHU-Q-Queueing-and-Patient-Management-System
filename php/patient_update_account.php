<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// 1. Get logged-in patient ID directly from session
$logged_id = $_SESSION['logged_id'] ?? 0;
$account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : $logged_id;

// Fallback if account_id wasn't posted
if ($account_id <= 0) {
    $account_id = $logged_id;
}

// 2. Authentication Check (Ensure user is logged in)
if ($logged_id <= 0 || $account_id <= 0) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Unauthorized access. Please log in again.'
    ]);
    exit;
}

function get_post_val($key, $default = null) {
    return (isset($_POST[$key]) && trim($_POST[$key]) !== '') ? trim($_POST[$key]) : $default;
}

$username         = get_post_val('username');
$fullname         = get_post_val('fullname');
$email            = get_post_val('email');
$current_password = get_post_val('current_password');
$new_password     = get_post_val('password');

if (!$username || !$fullname) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all require_onced fields.']);
    exit;
}

try {
    // Password Update Logic
    if (!empty($new_password)) {
        if (empty($current_password)) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is require_onced to set a new password.']);
            exit;
        }

        // Retrieve current password hash
        $passStmt = $conn->prepare("SELECT password_hash, password FROM patient_auth WHERE patient_auth_id = ?");
        $passStmt->bind_param("i", $account_id);
        $passStmt->execute();
        $res = $passStmt->get_result()->fetch_assoc();
        $passStmt->close();

        if (!$res) {
            echo json_encode(['status' => 'error', 'message' => 'Account record not found.']);
            exit;
        }

        $hashKey = !empty($res['password_hash']) ? 'password_hash' : 'password';

        if (!password_verify($current_password, $res[$hashKey])) {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect current password.']);
            exit;
        }

        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ["cost" => 12]);

        $sql = "UPDATE patient_auth SET username = ?, fullname = ?, email = ?, {$hashKey} = ? WHERE patient_auth_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $username, $fullname, $email, $hashed_password, $account_id);
    } else {
        // Basic Profile Update Logic
        $sql = "UPDATE patient_auth SET username = ?, fullname = ?, email = ? WHERE patient_auth_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $fullname, $email, $account_id);
    }

    if ($stmt->execute()) {
        // Update active session data
        $_SESSION['logged_account'] = $fullname;
        $_SESSION['logged_name'] = $username;

        // Log activity
        $logMsg = "Patient account " . $username . " updated their settings.";
        $logStmt = $conn->prepare("INSERT INTO logs (log_message) VALUES (?)");
        if ($logStmt) {
            $logStmt->bind_param("s", $logMsg);
            $logStmt->execute();
            $logStmt->close();
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Account settings updated successfully.'
        ]);
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