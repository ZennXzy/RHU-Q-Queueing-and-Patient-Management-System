<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once_once '../php/connection.php';

if (!isset($_SESSION['logged_id'])) {
    if (isset($_GET['ajax_check'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'unauthenticated']);
        exit();
    }
    header('Location: ../pages/login_page.php?msg=' . urlencode('Unauthenticated Access') . '&type=error');
    exit();
}

$user_id   = $_SESSION['logged_id'];
$user_type = $_SESSION['user_type'] ?? 'Admin'; // 'admin' or 'patient'

// Check online status in the appropriate table
if ($user_type === 'patient') {
    $stmt = $conn->prepare("SELECT status, username FROM patient_auth WHERE patient_auth_id = ?");
} else {
    $stmt = $conn->prepare("SELECT status, username FROM admin_auth WHERE id = ?");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    if (strtolower($row['status']) !== 'online') {
        session_unset();
        session_destroy();

        if (isset($_GET['ajax_check'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'logged_out']);
            exit();
        }

        header('Location: ../pages/login_page.php?msg=' . urlencode('Session Expired') . '&type=error');
        $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
        if ($insertlog) {
            $message = "Account " . $row['username'] . " logged out.";
            $insertlog->bind_param('s', $message);
            $insertlog->execute();
            $insertlog->close();
        }
        exit();
    }
} else {
    session_unset();
    session_destroy();
    
    if (isset($_GET['ajax_check'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'logged_out']);
        exit();
    }
    
    header('Location: ../pages/login_page.php?msg=' . urlencode('Logged Out') . '&type=info');
    exit();
}

$stmt->close();

if (isset($_GET['ajax_check'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'active']);
    exit();
}

if (!isset($_SESSION['logged_name']) || !isset($_SESSION['logged_id']) || !isset($_SESSION['logged_role'])) {
    header('Location: ../pages/login_page.php');
    exit();
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ../pages/login_page.php');
    exit();
}

function authorizeRoles(array $allowedRoles) {
    $userRole = $_SESSION['logged_role'] ?? '';

    if ($userRole === 'Admin') {
        return;
    }

    if (!in_array($userRole, $allowedRoles, true)) {
        
        if ($userRole === 'Display') {
            header('Location: ../pages/queue_display_page.php?msg=' . urlencode('Unauthorized access.') . '&type=error');
            exit();
        }

        if ($userRole === 'Doctor/Staff') {
            header('Location: ../pages/queue_manager_page.php?msg=' . urlencode('Unauthorized access.') . '&type=error');
            exit();
        } 

        // Handle Patient redirection on unauthorized attempt
        if ($userRole === 'Patient') {
            header('Location: ../pages/patient_dashboard.php?msg=' . urlencode('Unauthorized access.') . '&type=error');
            exit();
        }
        
        header('Location: ../pages/login_page.php?msg=' . urlencode('Unauthorized access.') . '&type=error');
        exit();
    }
}

function canManageRecords(): bool {
    $role = $_SESSION['logged_role'] ?? '';
    return $role === 'Admin';
}
?>