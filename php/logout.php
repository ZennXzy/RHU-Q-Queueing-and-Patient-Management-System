<?php
session_start();

session_destroy();
header('Location: ../pages/launch_page.php');
exit();
?>