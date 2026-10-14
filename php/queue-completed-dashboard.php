<?php
session_start();
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

$res = $conn->query("SELECT COUNT(*) AS completed_today_count FROM queue_information WHERE status = 'Completed' AND DATE(created_at) = CURDATE()");
$row = $res->fetch_assoc();

echo '<span class="stat-number">'.$row['completed_today_count'].'</span>';
?>