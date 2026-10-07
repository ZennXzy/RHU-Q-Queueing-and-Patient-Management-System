<?php
require '../api/check-private.php';
authorizeRoles(['Patient']);
require '../php/connection.php';

// Retrieve session identity data
$loggedId   = $_SESSION['patient_id'] ?? 0;
$fullname   = $_SESSION['logged_account'] ?? $_SESSION['logged_name'] ?? 'Unknown';
$loggedRole = $_SESSION['logged_role'] ?? $_SESSION['user_type'] ?? 'Unknown';
$currentPage = 'settings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RHU-Q: Patient Account Settings</title>
  <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
  
  <!-- Page Styles -->
  <link rel="stylesheet" href="../styles/account-settings.css">
  <link rel="stylesheet" href="../styles/account-settings_2.css">
  <link rel="stylesheet" href="../styles/patient_sidebar.css">
  <link rel="stylesheet" href="../styles/toast.css">
  <link rel="stylesheet" href="../styles/edit-account-form.css">

  <!-- Script Modules -->
  <script src="../js/toast.js" defer></script>
  <script src="../js/patient_edit_account.js" defer></script>
  <script src="../js/session-check.js" defer></script>
  <script src="../js/patient_sidebar.js" defer></script>
</head>
<body>

  <div class="workspace">
    <!-- Toast Notification Container -->
    <div class="toast-container" id="toastContainer"></div>


    <!-- Main Workspace Content -->
    <main class="content-card">
      <header class="top-bar">
          <div class="toast-container" id="toastContainer"></div>
          <?php include '../pages/patient_sidebar.php'; ?>
          <h1 class="page-title">Account Settings</h1>
      </header>
      <div class="account-card">  
        <div class="card-top-bar">
          <div class="account-header">
            <h2 id="displayFullName" class="account-title"><?= htmlspecialchars($fullname) ?></h2>
            <span id="displayRoleBadge" class="badge-role"><?= htmlspecialchars($loggedRole) ?></span>

          </div>
        </div>

        <hr class="divider">
        
        <!-- Patient Account Settings Form -->
        <form id="edit-account-form" class="settings-form">
          <input type="hidden" id="editAccountId" name="account_id" value="<?= htmlspecialchars($loggedId) ?>">
          <input type="hidden" id="editAccountRoleType" name="target_role" value="<?= htmlspecialchars($loggedRole) ?>">
          
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
              <input type="email" id="editEmail" name="email" class="form-control" placeholder="name@example.com">
            </div>

            <div class="form-group">
              <label for="editAccountToken">Account Token</label>
              <input type="text" id="editAccountToken" class="form-control" placeholder="Account Token" readonly disabled>
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
    </main>
  </div>

</body>
</html>