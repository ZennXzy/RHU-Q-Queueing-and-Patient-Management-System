<?php
session_start();

if (isset($_SESSION['logged_name']) && isset($_SESSION['logged_role'])) {
    $role = $_SESSION['logged_role'];

    if ($role === 'Display') {
        header('Location: ../pages/queue_display_page.php');
        exit();
    } else {
        header('Location: ../pages/login_page.php');
        exit();
    }
}
?>