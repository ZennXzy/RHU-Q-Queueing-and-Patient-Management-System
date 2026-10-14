<?php
session_start();
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

$res = $conn->query("SELECT COUNT(*) AS waiting_count FROM queue_information WHERE status = 'Waiting'");

$row = $res->fetch_assoc();
echo '<span class="stat-number" id="stat-number-waiting">';
echo $row['waiting_count'];
echo'</span>';
?>