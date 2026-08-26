<?php
session_start();
require '../php/connection.php';

$res = $conn->query("SELECT COUNT(*) AS completed_today_count FROM queue_information WHERE status = 'Completed' AND DATE(created_at) = CURDATE()");
$row = $res->fetch_assoc();

echo '<span class="stat-number">'.$row['completed_today_count'].'</span>';
?>