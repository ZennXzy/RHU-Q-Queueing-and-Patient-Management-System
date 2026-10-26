<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php'; 
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;
$logged_id  = $_SESSION['logged_id'] ?? 0;
$logged_role = $_SESSION['logged_role'] ?? '';

if ($account_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing Account ID.']);
    exit;
}

// Security Check: Users can only edit their own account unless they are Admin
if ($logged_role !== 'Admin' && $logged_id !== $account_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized action.']);
    exit;
}

function get_post_val($key, $default = null) {
    return (isset($_POST[$key]) && trim($_POST[$key]) !== '') ? trim($_POST[$key]) : $default;
}

$username = get_post_val('username');
$fullname = get_post_val('fullname');
$email    = get_post_val('email');
$role     = get_post_val('role', $logged_role);
$current_password = get_post_val('current_password');
$new_password     = get_post_val('password');
$target_role      = get_post_val('target_role', $role);

$table = ($target_role === 'Patient') ? 'patient_auth' : 'admin_auth';
$id_field = ($target_role === 'Patient') ? 'patient_auth_id' : 'id';

// Verify field column existence for patient_auth
if ($target_role === 'Patient') {
    $checkCol = $conn->query("SHOW COLUMNS FROM patient_auth LIKE 'patient_auth_id'");
    if (!$checkCol || $checkCol->num_rows === 0) {
        $id_field = 'id';
    }
}

try {
    // Check if user attempts password update
    if (!empty($new_password)) {
        if ($logged_role !== 'Admin' && empty($current_password)) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is require_onced to change password.']);
            exit;
        }

        if ($logged_role !== 'Admin') {
            $passStmt = $conn->prepare("SELECT password FROM {$table} WHERE {$id_field} = ?");
            $passStmt->bind_param("i", $account_id);
            $passStmt->execute();
            $res = $passStmt->get_result()->fetch_assoc();
            $passStmt->close();

            if (!$res || !password_verify($current_password, $res['password'])) {
                echo json_encode(['status' => 'error', 'message' => 'Incorrect current password.']);
                exit;
            }
        }

        $options = ["cost" => 12];
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, $options);
        
        if ($target_role === 'Patient') {
            $sql = "UPDATE patient_auth SET username = ?, email = ?, password_hash = ? WHERE {$id_field} = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $username, $email, $hashed_password, $account_id);
        } else {
            $sql = "UPDATE admin_auth SET username = ?, fullname=?, email = ?, role = ?, password_hash = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $username, $fullname, $email, $role, $hashed_password, $account_id);
        }
    } else {
        if ($target_role === 'Patient') {
            $sql = "UPDATE patient_auth SET username = ?, email = ? WHERE {$id_field} = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $username, $email, $account_id);
        } else {
            $sql = "UPDATE admin_auth SET username = ?, fullname = ?, email = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $username, $fullname, $email, $role, $account_id);
        }
    }

    if ($stmt->execute()) {
        if ($logged_id === $account_id) {
            $_SESSION['logged_account'] = $fullname;
        }

        $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
        $message = "Account " . $username . " has been updated.";
        if ($insertlog) {
            $insertlog->bind_param('s', $message);
            $insertlog->execute();
            $insertlog->close();
        }    
        echo json_encode([
            'status' => 'success',
            'message' => 'Account updated successfully.'
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