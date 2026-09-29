<?php
require '../api/check-private.php';
authorizeRoles(['Admin', 'Doctor/Staff', 'Patient']);
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
    <script src="../js/toast.js" defer></script>
    <script src="../js/edit-account.js" defer></script>
    <script src="../js/session-check.js" defer></script>
</head>
<body>
    <?php include '../pages/top-navbar.php'; ?>

  <div class="workspace">
    <div class="toast-container" id="toastContainer"></div>
    <?php include '../pages/sidebar.php'; ?>

    <div class="content-card">
      <h1 class="page-title">Account Settings</h1>
      
      <div class="account-card">  
        <div class="card-top-bar">
          <div class="account-header">
            <?php
              $fullname = $_SESSION['logged_account'] ?? '';
              $role = $_SESSION['logged_role'] ?? '';
              echo '<h2 id="displayFullName" class="account-title">'.htmlspecialchars($fullname).'</h2>';
              echo '<span id="displayRoleBadge" class="badge-role">'.htmlspecialchars($role).'</span>';
            ?>
          </div>
          <?php if(($_SESSION['logged_role'] ?? '') === "Admin"){ ?>
          <div class="top-action-button">
            <button type="button" class="btn btn-secondary" onclick="location.href='backup.php'">
              Backup and Restore
            </button>
            <button type="button" class="btn btn-secondary" onclick="location.href='logs.php'">
              Logs
            </button>
          </div> 
          <?php } ?>
        </div>

        <hr class="divider">
        
        <!-- Account Settings Form -->
        <form id="edit-account-form" class="settings-form">
          <input type="hidden" id="editAccountId" name="account_id" value="<?php echo $_SESSION['logged_id']; ?>">
          <input type="hidden" id="editAccountRoleType" name="target_role" value="<?php echo $_SESSION['logged_role']; ?>">
          
          <div class="form-grid">
            <!-- Username Field -->
            <div class="form-group">
              <label for="editUsername">Username</label>
              <input type="text" id="editUsername" name="username" class="form-control" placeholder="Enter username" required>
            </div>

            <!-- Full Name Field -->
            <div class="form-group">
              <label for="editFullname">Full Name</label>
              <input type="text" id="editFullname" name="fullname" class="form-control" placeholder="Enter full name" required>
            </div>

            <!-- Email Field -->
            <div class="form-group">
              <label for="editEmail">Email Address</label>
              <input type="email" id="editEmail" name="email" class="form-control" placeholder="name@example.com" required>
            </div>

            <!-- Account Role -->
            <?php if(($_SESSION['logged_role'] ?? '') === "Admin"){ ?>
            <div class="form-group">
              <label for="editRole">Account Role</label>
              <select id="editRole" name="role" class="service-type">
                <option value="Admin">Admin</option>
                <option value="Doctor/Staff">Doctor/Staff</option>
                <option value="Display">Display</option>
              </select>
            </div>
            <?php } else { ?>
            <div class="form-group">
              <label for="editRoleDisplay">Account Role</label>
              <input type="text" id="editRoleDisplay" class="form-control" value="<?php echo htmlspecialchars($_SESSION['logged_role'] ?? ''); ?>" readonly disabled>
              <input type="hidden" id="editRole" name="role" value="<?php echo htmlspecialchars($_SESSION['logged_role'] ?? ''); ?>">
            </div>
            <?php } ?>

            <!-- Password Section Header -->
            <div class="form-section-title">Change Password</div>

            <!-- Current Password -->
            <div class="form-group">
              <label for="current_password">Current Password</label>
              <input type="password" id="current_password" name="current_password" class="form-control" placeholder="••••••••">
            </div>

            <!-- New Password -->
            <div class="form-group">
              <label for="editPassword">New Password</label>
              <input type="password" id="editPassword" name="password" class="form-control" placeholder="Leave blank to keep current">
            </div>
          </div>

          <!-- Form Action Buttons -->
          <div class="form-actions">
            <button type="reset" id="closeBtn" class="btn-cancel">Cancel</button>
            <button type="submit" class="btn-submit">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>