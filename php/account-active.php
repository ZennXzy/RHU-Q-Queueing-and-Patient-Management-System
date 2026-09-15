<?php
session_start();
require '../php/connection.php';

$res = $conn->query("SELECT COUNT(*) AS Online FROM admin_auth WHERE status = 'Online'");
$row = $res->fetch_assoc();

echo '<strong class="stat-value" id="stat-card-active">'.$row['Online'].'</strong>';
?>