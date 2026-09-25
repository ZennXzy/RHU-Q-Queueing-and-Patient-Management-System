<?php
require '../api/check-private.php';
authorizeRoles(['Doctor/Staff']);
require '../php/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RHU-Q: Account Manager</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/account-settings.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/toast.css">
    <link rel="stylesheet" href="../styles/edit-account-form.css">


    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/add-account.js" defer></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/edit-account.js" defer></script>
    <script src="../js/session-check.js" defer></script>

</head>
<body>
    <?php
        include '../pages/top-navbar.php';
    ?>

  <div class="workspace">
    <div class="toast-container" id="toastContainer"></div>
    <?php
        include '../pages/sidebar.php';
    ?>

    <div class="content-area">
      <h1 class="page-title">Settings</h1>

      <div class="content-card">
        <div class="card-top-bar">
          <div class="top-action-button">
            <button class="btn btn-secondary"> Backup and Restore
            </button>
            <button class="btn btn-secondary">
              Logs
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>