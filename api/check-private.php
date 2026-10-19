<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set the correct local timezone so date('H:i') matches your local time
date_default_timezone_set('Asia/Manila');

require '../php/connection.php';

// 1. Check if user is authenticated
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
$user_type = strtolower($_SESSION['user_type'] ?? $_SESSION['logged_role'] ?? 'patient');

// 2. Specific Time Auto-Logout Check
$currentHourMin = date('H:i'); // Now correctly matches local time
if ($user_type !== 'admin') { 
    $cutoffTime = '23:30'; // Set your target closing time (24-hour format)
    if ($currentHourMin >= $cutoffTime) {
        // Update user status to 'Offline' in database
        if ($user_type === 'patient') {
            $updateStmt = $conn->prepare("UPDATE patient_auth SET status = 'Offline' WHERE patient_auth_id = ?");
        } else {
            $updateStmt = $conn->prepare("UPDATE admin_auth SET status = 'Offline' WHERE id = ?");
        }
        $updateStmt->bind_param("i", $user_id);
        $updateStmt->execute();
        $updateStmt->close();

        // Clear session and destroy
        session_unset();
        session_destroy();

        if (isset($_GET['ajax_check'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'logged_out', 'reason' => 'Clinic closed']);
            exit();
        }

        header('Location: ../pages/login_page.php?msg=' . urlencode('System closed for the day. Logged out.') . '&type=error');
        exit();
    }
}  else if ($user_type == 'admin'){
    $cutoffTime = '23:50'; // Set your target closing time (24-hour format)
    if ($currentHourMin >= $cutoffTime) {
        // Update user status to 'Offline' in database
        $updateStmt = $conn->prepare("UPDATE admin_auth SET status = 'Offline' WHERE id = ?");
        $updateStmt->bind_param("i", $user_id);
        $updateStmt->execute();
        $updateStmt->close();

        // Clear session and destroy
        session_unset();
        session_destroy();

        if (isset($_GET['ajax_check'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'logged_out', 'reason' => 'Clinic closed']);
            exit();
        }

        header('Location: ../pages/login_page.php?msg=' . urlencode('System closed for the day. Logged out.') . '&type=error');
        exit();
    }
}

// 3. Check active database status for all users
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
    
    header('Location: ../pages/login_page.php?msg=' . urlencode('Session Expired') . '&type=error');
    exit();
}
$stmt->close();

// 4. Handle AJAX background heartbeat check from session-check.js
if (isset($_GET['ajax_check'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'active']);
    exit();
}

// 5. Role Authorization Function used across pages
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