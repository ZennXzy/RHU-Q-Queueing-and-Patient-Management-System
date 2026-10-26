<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// 1. Get logged-in patient ID directly from session[cite: 4]
$logged_id = $_SESSION['logged_id'] ?? 0;
$account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : $logged_id;

// Fallback if account_id wasn't posted[cite: 4]
if ($account_id <= 0) {
    $account_id = $logged_id;
}

// 2. Authentication Check (Ensure user is logged in)[cite: 4]
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
$email            = get_post_val('email');
$current_password = get_post_val('current_password');
$new_password     = get_post_val('password');

if (!$username) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
    exit;
}

try {
    // Fetch the related patient_id from patient_auth
    $idStmt = $conn->prepare("SELECT patient_id FROM patient_auth WHERE patient_auth_id = ?");
    $idStmt->bind_param("i", $account_id);
    $idStmt->execute();
    $idRes = $idStmt->get_result()->fetch_assoc();
    $idStmt->close();

    if (!$idRes) {
        echo json_encode(['status' => 'error', 'message' => 'Account record not found.']);
        exit;
    }
    $patient_id = $idRes['patient_id'];

    // Begin transaction to handle both updates atomically
    $conn->begin_transaction();

    if (!empty($new_password) || !empty($current_password)) {
        if (empty($current_password)) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Current password is required to change your password.']);
            exit;
        }

        if (empty($new_password)) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Please provide a new password.']);
            exit;
        }

        // Retrieve current password hash
        $passStmt = $conn->prepare("SELECT password_hash FROM patient_auth WHERE patient_auth_id = ?");
        $passStmt->bind_param("i", $account_id);
        $passStmt->execute();
        $res = $passStmt->get_result()->fetch_assoc();
        $passStmt->close();

        if (!$res) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Account record not found.']);
            exit;
        }

        // Verify the current password (will catch incorrect or random text)
        if (!password_verify($current_password, $res['password_hash'])) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Incorrect current password.']);
            exit;
        }
        
        $options = ["cost" => 12];
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, $options);

        // Update patient_auth including the new password hash
        $sql = "UPDATE patient_auth SET username = ?, password_hash = ? WHERE patient_auth_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $hashed_password, $account_id);
    } else {
        // Basic Profile Update Logic (when both password fields are completely empty)
        $sql = "UPDATE patient_auth SET username = ? WHERE patient_auth_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $account_id);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execution failed for patient_auth: " . $stmt->error);
    }
    $stmt->close();

    // Condition: If email is provided and verified as a string, update patient_records instead of patient_auth
    if (!empty($email) && is_string($email)) {
        $emailStmt = $conn->prepare("UPDATE patient_records SET email = ? WHERE patient_id = ?");
        $emailStmt->bind_param("si", $email, $patient_id);
        if (!$emailStmt->execute()) {
            throw new Exception("Execution failed for patient_records email update: " . $emailStmt->error);
        }
        $emailStmt->close();
    }

    $conn->commit();

    // Update active session data[cite: 4]
    $_SESSION['logged_name'] = $username;

    // Log activity[cite: 4]
    $logMsg = "Patient account " . $username . " updated their account.";
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

} catch (Exception $e) {
    if ($conn->in_transaction) {
        $conn->rollback();
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>