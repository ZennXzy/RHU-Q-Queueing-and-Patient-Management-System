<?php
  session_start();

  if(isset($_SESSION['logged_name'])){
    header('Location: ../pages/register_page.php');
  }

?>