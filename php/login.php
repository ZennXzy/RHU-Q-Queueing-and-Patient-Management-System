<?php
session_start();
require_once 'connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['login'])){
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($username === '' || $password === ''){
    header('Location: ../pages/login_page.php?msg=' . urlencode('Username and password are require_onced') . '&type=error');
    exit();
  }

  // ------------------------------------------------------------------
  // 1. Try finding user in admin_auth table
  // ------------------------------------------------------------------
  $stmt = $conn->prepare('SELECT id, username, fullname, password_hash, status, role, NULL AS patient_id FROM admin_auth WHERE username = ?');
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $res = $stmt->get_result();
  $user = $res ? $res->fetch_assoc() : null;
  $userType = 'admin';
  $stmt->close();

  // ------------------------------------------------------------------
  // 2. If not found in admin_auth, search in patient_auth table
  // ------------------------------------------------------------------
  if (!$user) {
      $stmt = $conn->prepare('SELECT patient_auth_id AS id, username, fullname, password_hash, status, role, patient_id FROM patient_auth WHERE username = ?');
      $stmt->bind_param('s', $username);
      $stmt->execute();
      $res = $stmt->get_result();
      $user = $res ? $res->fetch_assoc() : null;
      $userType = 'patient';
      $stmt->close();
  }

  // ------------------------------------------------------------------
  // 3. Verify Account & Credentials
  // ------------------------------------------------------------------
  if ($user) {
      if (password_verify($password, $user['password_hash'])) {

          if (strtolower($user['status']) === 'online') {
              header('Location: ../pages/login_page.php?msg=' . urlencode('User is already online in another session.') . '&type=error');
              exit();
          }

          // Update online status in the corresponding table
          if ($userType === 'admin') {
              $update_stmt = $conn->prepare("UPDATE admin_auth SET status = 'Online', last_active = NOW() WHERE id = ?");
          } else {
              $update_stmt = $conn->prepare("UPDATE patient_auth SET status = 'Online', last_active = NOW() WHERE patient_auth_id = ?");
          }
          $update_stmt->bind_param("i", $user['id']);
          $update_stmt->execute();
          $update_stmt->close();

          // Save details to Session
          $_SESSION['logged_id']      = $user['id'];
          $_SESSION['logged_name']    = $user['username'];
          $_SESSION['logged_role']    = $user['role'];
          $_SESSION['logged_account'] = $user['fullname'];
          $_SESSION['user_type']      = $userType; // 'admin' or 'patient'
          if ($userType === 'patient') {
              $_SESSION['patient_id'] = $user['patient_id'];
          }

          // Insert into activity logs
          $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
          if ($insertlog) {
              $message = $user['username'] . " (" . ucfirst($userType) . ") logged in successfully.";
              $insertlog->bind_param('s', $message);
              $insertlog->execute();
              $insertlog->close();
          }

          // Redirect based on user type/role
          if ($userType === 'Patient') {
              header('Location: ../pages/queue_display_page.php?msg=' . urlencode('Logged in successfully') . '&type=success');
          } else {
              header('Location: ../pages/launch_page.php?msg=' . urlencode('Logged in successfully') . '&type=success');
          }
          exit();

      } else {
          header('Location: ../pages/login_page.php?msg=' . urlencode('Invalid credentials') . '&type=error');
          exit();
      }
  } else {
      header('Location: ../pages/login_page.php?msg=' . urlencode('User not found') . '&type=error');
      exit();
  }
}
$conn->close();
?>