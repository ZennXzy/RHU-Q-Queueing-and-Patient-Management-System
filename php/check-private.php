<?php
  session_start();

  if(!isset($_SESSION['logged_name']) or !isset($_SESSION['logged_id'])){
    header('Location: ../pages/login_page.php');
  }

  function runMyFunction(){
    unset($_SESSION['logged_name']);
    unset($_SESSION['logged_id']);
    header('Location: ../pages/login_page.php');
  }

  if(isset($_GET['logout'])){
    runMyFunction();
  }
?>