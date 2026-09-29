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

    <div class="content-card">
      <h1 class="page-title">Account Settings</h1>
      <!-- Top Bar with Navigation / Action Buttons -->
      <div class="account-card">  
        <div class="card-top-bar">
          <div class="account-header">
            <?php
              $fullname = $_SESSION['logged_account'];
              $role = $_SESSION['logged_role'];
              echo '<h2 id="displayFullName" class="account-title">'.$fullname.'</h2>';
              echo '<span id="displayRoleBadge" class="badge-role">'.$role.'</span>';
            ?>
          </div>
          <?php if($_SESSION['logged_role'] === "Admin"){ ?>
          <div class="top-action-button">
            <button type="button" class="btn btn-secondary" onclick="location.href='backup.php'">
              Backup and Restore
            </button>
            <button type="button" class="btn btn-secondary" onclick="location.href='logs.php'">
              Logs
            </button>
          </div> <?php }?>
          
        </div>

        <hr class="divider">
        
        <!-- Account Settings Form -->
          <form id="accountSettingsForm" class="settings-form" method="POST" action="../api/update_account.php">
            <div class="form-grid">
              
              <!-- Username Field -->
              <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required>
              </div>

              <!-- Full Name Field -->
              <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Enter full name" required>
              </div>

              <!-- Email Field -->
              <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" required>
              </div>

              <!-- Account Role (Disabled / Read-Only for Security) -->
              <div class="form-group">
                <label for="account_role">Account Role</label>
                <input type="text" id="account_role" name="account_role" class="form-control" readonly disabled>
              </div>

              <!-- Password Section Header -->
              <div class="form-section-title">Change Password</div>

              <!-- Current Password -->
              <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="form-control" placeholder="••••••••">
              </div>

              <!-- New Password -->
              <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Leave blank to keep current">
              </div>

            </div>

            <!-- Form Action Buttons -->
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Save Changes</button>
              <button type="reset" class="btn btn-outline">Cancel</button>
            </div>
          </form>
      </div>
    </div>
  </div>
</body>
</html>