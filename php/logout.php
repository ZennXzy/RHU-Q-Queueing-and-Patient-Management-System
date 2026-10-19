<?php
session_start();
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

$role = $_SESSION['user_type'] ?? $_SESSION['logged_role'] ?? 'Patient';
$username = $_SESSION['logged_name'] ?? $_SESSION['username'] ?? null;
$patientId = $_SESSION['patient_id'] ?? $_SESSION['user_id'] ?? null;

if ($username || $patientId) {
    switch (strtolower($role)) {
        case 'admin':
        case 'doctor/staff':
            // Update admin / staff account status
            $stmt = $conn->prepare("UPDATE admin_auth SET status = 'Offline' WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();
            break;

        case 'patient':
        default:
            // Update patient status in patients table
            if ($patientId) {
                $stmt = $conn->prepare("UPDATE patient_auth SET status = 'Offline' WHERE patient_id = ?");
                $stmt->bind_param("i", $patientId);
            }
            $stmt->execute();
            $stmt->close();
            break;
    }

    // Record logout action in system logs
    $logMessage = ($username ?? "Patient ID: $patientId") . " logged out.";
    $insertLog = $conn->prepare("INSERT INTO logs (log_message) VALUES (?)");
    if ($insertLog) {
        $insertLog->bind_param("s", $logMessage);
        $insertLog->execute();
        $insertLog->close();
    }
}

// Clear session cookies and destroy active session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_unset();
session_destroy();

header('Location: ../index.html');
exit();
?>