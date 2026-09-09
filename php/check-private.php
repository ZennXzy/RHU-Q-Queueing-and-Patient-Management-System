<?php
session_start();

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
        
        header('Location: ../pages/login_page.php?msg=' . urlencode('Unauthorized access.') . '&type=error');
        exit();
    }
}

function canManageRecords(): bool {
    $role = $_SESSION['logged_role'] ?? '';
    return $role === 'Admin';
}
?>