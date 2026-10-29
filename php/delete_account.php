<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';
date_default_timezone_set('Asia/Manila');
authorizeRoles(['Admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_SESSION['logged_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized session. Please log in again.']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$type = isset($_POST['type']) ? trim($_POST['type']) : 'staff';
$entered_password = isset($_POST['admin_password']) ? trim($_POST['admin_password']) : '';

if ($id <= 0 || empty($entered_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing account ID or password.']);
    exit;
}

// Prevent admins from deleting their own active account
if ($type === 'staff' && $id === intval($_SESSION['logged_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You cannot delete your own active admin account.']);
    exit;
}

$user_name = $_SESSION['logged_name'] ?? '';

try {
    // Verify admin password from admin_auth[cite: 20]
    $stmt = $conn->prepare("SELECT password_hash FROM admin_auth WHERE username = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (!password_verify($entered_password, $row['password_hash'])) {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect authorization password.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Admin account not found.']);
        exit;
    }
    $stmt->close();

    // Perform deletion based on account type ($type)
    if ($type === 'staff') {
        $deleteStmt = $conn->prepare("DELETE FROM admin_auth WHERE id = ?");
    } else {
        $deleteStmt = $conn->prepare("DELETE FROM patient_auth WHERE patient_auth_id = ?");
    }
    
    $deleteStmt->bind_param("i", $id);

    if ($deleteStmt->execute()) {
        if ($deleteStmt->affected_rows > 0) {
            $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
            if ($insertlog) {
                $message = $user['username'] . " (" . ucfirst($userType) . ") deleted an account from ". $type;
                $insertlog->bind_param('s', $message);
                $insertlog->execute();
                $insertlog->close();
            }
            echo json_encode(['status' => 'success', 'message' => 'Account successfully deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Account not found or already deleted.']);
        }
    } else {
        throw new Exception($deleteStmt->error);
    }
    $deleteStmt->close();

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>