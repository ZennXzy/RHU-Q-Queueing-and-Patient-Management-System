<?php
  $hostname = getenv('DB_HOST') ?: "localhost";
  $username = getenv('DB_USER') ?: "root";
  $password = getenv('DB_PASS') !== false && getenv('DB_PASS') !== '' ? getenv('DB_PASS') : "";
  $databasename = getenv('DB_NAME') ?: "rhu_q_db";
  $port = getenv('DB_PORT') ? (int)getenv('DB_PORT') : 3306;

  $conn = new mysqli($hostname, $username, $password, $databasename, $port);

  if($conn -> connect_error){
    die("Error ".$conn->connect_error. "<br>");
  }
?>