<?php
session_start();
require 'connection.php';

if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['login'])){
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  if (trim($username) === '' || trim($password) === ''){
    header('Location: ../pages/login_page.php?msg=' . urlencode('Username and password are required') . '&type=error');
    exit;
  }

  if (strlen($password) <= 10) {
    header('Location: ../pages/login_page.php?msg=' . urlencode('Password must be more than 10 characters') . '&type=error');
    exit;
  }

  $stmt = $conn->prepare('SELECT id, username, password_hash FROM admin_auth WHERE username = ?');
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows > 0){
    $row = $res->fetch_assoc();
    $user_id = $row['id'];
    $sysUsername = $row['username'];
    $sysPassword = $row['password_hash'];
    
    if (password_verify($password, $sysPassword)){
      $_SESSION['logged_id'] = $user_id;
      $_SESSION['logged_name'] = $sysUsername;

      header('Location: ../pages/launch_page.php?msg=' . urlencode('Logged in successfully') . '&type=success');
      exit();
    } 
    else {
      header('Location: ../pages/login_page.php?msg=' . urlencode('Invalid credentials') . '&type=error');
      exit();
    }
  }
}

$conn->close();
?>
