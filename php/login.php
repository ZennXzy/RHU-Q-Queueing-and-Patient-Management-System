<?php
session_start();
require_once 'connection.php';

if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['login'])){
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  $status = "Offline";

  if (trim($username) === '' || trim($password) === ''){
    header('Location: ../pages/login_page.php?msg=' . urlencode('Username and password are required') . '&type=error');
    exit();
  }

  $stmt = $conn->prepare('SELECT id, username, password_hash, status, role FROM admin_auth WHERE username = ?');
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows > 0){
    $row = $res->fetch_assoc();
    $user_id = $row['id'];
    $sysUsername = $row['username'];
    $role = $row['role'];
    $sysPassword = $row['password_hash'];
    
    if (password_verify($password, $sysPassword)){
      if($row['status'] === $status){
        
        $_SESSION['logged_id'] = $user_id;
        $_SESSION['logged_name'] = $sysUsername;
        $_SESSION['logged_role'] = $role;

        $update_stmt = $conn->prepare("UPDATE admin_auth SET status = 'Online' WHERE username = ?");
        $update_stmt->bind_param("s", $username);
        $update_stmt->execute();

        $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
        if ($insertlog) {
            $message = "Account " . $username . " logged in successfully.";
            $insertlog->bind_param('s', $message);
            $insertlog->execute();
            $insertlog->close();
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Account '.$sysUsername.' logged in successfully.'
        ]);
        header('Location: ../pages/launch_page.php?msg=' . urlencode('Logged in successfully') . '&type=success');
        exit();
      } else {
        header('Location: ../pages/login_page.php?msg=' . urlencode('User is Online.') . '&type=error');
        exit();
      }
    }
    else {
      header('Location: ../pages/login_page.php?msg=' . urlencode('Invalid credentials') . '&type=error');
      exit();
    }
  } 
  else {
    header('Location: ../pages/login_page.php?msg=' . urlencode('User not found') . '&type=error');
    exit();
  }

  // if ($res && $res->num_rows === 0){
  //   $options = ["cost" => 12];
  //   $encryptedpassword = password_hash($password, PASSWORD_BCRYPT, $options);

  //   $insert = $conn->prepare('INSERT INTO admin_auth (username, password_hash) VALUES (?,?)');
  //   $insert->bind_param('ss', $username, $encryptedpassword);
  //   if ($insert->execute()){
  //     $newId = $insert->insert_id;
  //     $_SESSION['logged_id'] = $newId;
  //     $_SESSION['logged_name'] = $username;
  //     header('Location: ../pages/launch_page.php?msg=' . urlencode('Account created and logged in') . '&type=success');
  //     exit();
  //   } else {
  //     header('Location: ../pages/login_page.php?msg=' . urlencode('Error creating account') . '&type=error');
  //     exit();
  //   }
  // }
}
$conn->close();
?>
