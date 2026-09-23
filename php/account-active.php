<?php
session_start();
require '../php/connection.php';

$excluded = $_SESSION['logged_id'];
$stmt = $conn->prepare("SELECT COUNT(*) AS Online FROM admin_auth WHERE status = 'Online' AND id != ?");
$stmt->bind_param("i", $excluded);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

echo '<strong class="stat-value" id="stat-card-active">'.$row['Online'].'</strong>';
?>