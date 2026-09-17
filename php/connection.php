<?php
  $hostname = "localhost";
  $username = "root";
  $password = "";
  $databasename = "rhu_q_db";

  $conn = new mysqli($hostname, $username, $password, $databasename);

  if($conn -> connect_error){
    die("Error ".$conn->connect_error. "<br>");
  }
?>