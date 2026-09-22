<?php
header('Content-Type: application/json');
require_once '../php/connection.php'; 
require_once '../api/check-private.php';

if (!canManageRecords()) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Unauthorized action. Admin privileges required.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;

if ($account_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing Account ID.'
    ]);
    exit;
}

function get_post_val($key, $default = null) {
    if (isset($_POST[$key]) && trim($_POST[$key]) !== '') {
        return trim($_POST[$key]);
    }
    return $default;
}

$username = get_post_val('username');
$fullname = get_post_val('fullname');
$email    = get_post_val('email');
$role     = get_post_val('role');
$password = get_post_val('password');

if (!$username || !$fullname || !$email || !$role) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please fill in all required fields (Username, Full Name, Email, and Role).'
    ]);
    exit;
}

try {
    if (!empty($password)) {
        $options = ["cost" => 12];
        $hashed_password = password_hash($password, PASSWORD_BCRYPT, $options);
        
        $sql = "UPDATE admin_auth SET 
                    username = ?, 
                    fullname = ?, 
                    email = ?, 
                    role = ?, 
                    password = ? 
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("sssssi", $username, $fullname, $email, $role, $hashed_password, $account_id);
    } else {
        $sql = "UPDATE admin_auth SET 
                    username = ?, 
                    fullname = ?, 
                    email = ?, 
                    role = ? 
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param("ssssi", $username, $fullname, $email, $role, $account_id);
    }

    if ($stmt->execute()) {
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