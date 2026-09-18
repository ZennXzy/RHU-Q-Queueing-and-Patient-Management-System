<?php
session_start();
require_once 'connection.php';

if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['login'])){
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  if (trim($username) === '' || trim($password) === ''){
    header('Location: ../pages/login_page.php?msg=' . urlencode('Username and password are required') . '&type=error');
    exit();
  }

  $stmt = $conn->prepare('SELECT id, username, fullname, password_hash, status, role FROM admin_auth WHERE username = ?');
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        
        if (password_verify($password, $row['password_hash'])) {

            if (strtolower($row['status']) === 'online') {
                header('Location: ../pages/login_page.php?msg=' . urlencode('User is already online in another session.') . '&type=error');
                exit();
            }
            $update_stmt = $conn->prepare("UPDATE admin_auth SET status = 'Online', last_active = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $row['id']);
            $update_stmt->execute();
            $update_stmt->close();

            $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
            if ($insertlog) {
                $_SESSION['logged_id']   = $row['id'];
                $_SESSION['logged_name'] = $row['username'];
                $_SESSION['logged_role'] = $row['role'];
                $_SESSION['logged_account'] = $row['fullname'];

                $message = $row['username'] . " logged in successfully.";
                $insertlog->bind_param('s', $message);
                $insertlog->execute();
                $insertlog->close();
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
