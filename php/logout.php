<?php
session_start();
require_once '../php/connection.php';

$username = $_SESSION['logged_name'];

$res = $conn->prepare("UPDATE admin_auth SET status = 'Offline' WHERE username = ?");
$res->bind_param("s", $username);
if ($res->execute()) {
    if ($res->affected_rows > 0) {
        $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
        if ($insertlog) {
            $message = $username . " just logged out.";
            $insertlog->bind_param('s', $message);
            $insertlog->execute();
            $insertlog->close();
        }
        echo json_encode([
            'status' => 'success',
            'message' => 'User Logged Out.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Logout failed.'
        ]);
    }
} else {
    throw new Exception($res->error);
}

$res->close();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}


session_destroy();
header('Location: ../pages/login_page.php');
exit();
?>