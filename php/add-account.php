<?php
header('Content-Type: application/json');
require_once '../php/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}
date_default_timezone_set('Asia/Manila');
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
$email    = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$status   = "Offline";
$role     = "Doctor/staff";

// 1. Validation checks
if (empty($username) || empty($fullname) || empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all require_onced fields.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid email address.']);
    exit();
}

if (strlen($password) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long.']);
    exit();
}

try {
    $stmt = $conn->prepare('SELECT username, fullname, email FROM admin_auth WHERE username = ? OR fullname = ? OR email = ?');
    $stmt->bind_param('sss', $username, $fullname, $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            if ($row['username'] === $username) {
                echo json_encode(['status' => 'error', 'message' => 'Username is already taken.']);
                exit();
            }
            if ($row['fullname'] === $fullname) {
                echo json_encode(['status' => 'error', 'message' => 'User is already registered.']);
                exit();
            }
            if ($row['email'] === $email) {
                echo json_encode(['status' => 'error', 'message' => 'Email address is already in use.']);
                exit();
            }
        }
    }
    $stmt->close();

    $options = ["cost" => 12];
    $encryptedpassword = password_hash($password, PASSWORD_BCRYPT, $options);

    $insert = $conn->prepare('INSERT INTO admin_auth (username, fullname, email, password_hash, status, role) VALUES (?, ?, ?, ?, ?, ?)');
    $insert->bind_param('ssssss', $username, $fullname, $email, $encryptedpassword, $status, $role);

    if ($insert->execute()) {
        $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
        if ($insertlog) {
            $message = "Account " . $username . " created successfully.";
            $insertlog->bind_param('s', $message);
            $insertlog->execute();
            $insertlog->close();
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Account created successfully.'
        ]);
        exit();
    } else {
        throw new Exception($insert->error);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit();
}

$conn->close();
?>