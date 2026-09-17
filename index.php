<?php
session_start();
if (isset($_SESSION['logged_id'])) {
    header("Location: /pages/launch_page.php");
} else {
    header("Location: /pages/login_page.php");
}
exit();
?>
