<?php
session_start();
require_once '../php/connection.php';

  $res = $conn->query("SELECT COUNT(*) AS in_progress_count FROM queue_information WHERE status = 'In Progress'");
  $row = $res->fetch_assoc();

  echo '<span class="stat-number" id="stat-number-in-progress">'.$row['in_progress_count'].'</span>';
?>  
  