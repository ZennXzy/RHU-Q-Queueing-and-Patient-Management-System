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
    <link rel="stylesheet" href="../styles/account-settings_2.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/toast.css">
    <link rel="stylesheet" href="../styles/edit-account-form.css">

    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/edit-account.js" defer></script>
    <script src="../js/backup_modal.js" defer></script>
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
            <button type="button" class="btn btn-secondary" id="openBackupModalBtn">
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
  <!-- backup/restore modal -->
   <dialog class="backupModal" id="backupModal">
    <!-- Modal Header -->
    <div class="custom-modal-header">
      <h5 class="custom-modal-title">DATABASE BACKUP</h5>
      <button type="button" class="custom-modal-close" id="closeBackupModalBtn">&times;</button>
    </div>

    <!-- Modal Body / Form -->
    <form action="backup.php" method="POST" id="backupForm" class="backupForm">
      <div class="custom-modal-body">
        
        <!-- Step 1: Backup Format Type -->
        <div class="section-group">
          <span class="section-label">1. Select Backup Type / Format</span>
          <div class="format-grid">
            
            <label class="format-card">
              <input type="radio" name="backup_type" value="sql" checked>
              <div class="format-card-content">
                <strong>Full Database Backup</strong>
                <span class="format-ext">.sql</span>
                <small>Standard SQL database dump for full structural restores.</small>
              </div>
            </label>

            <label class="format-card">
              <input type="radio" name="backup_type" value="zip">
              <div class="format-card-content">
                <strong>Compressed Backup</strong>
                <span class="format-ext">.zip</span>
                <small>Gzipped archive for space-efficient storage.</small>
              </div>
            </label>

            <label class="format-card">
              <input type="radio" name="backup_type" value="csv">
              <div class="format-card-content">
                <strong>Data Export</strong>
                <span class="format-ext">.csv</span>
                <small>Export individual tables into raw CSV spreadsheets.</small>
              </div>
            </label>

          </div>
        </div>

        <hr class="modal-divider">

        <!-- Step 2: Table Selection -->
        <div class="section-group">
          <div class="table-header-row">
            <span class="section-label">2. Select Tables to Backup</span>
            <label class="checkbox-label select-all-label">
              <input type="checkbox" id="selectAllTables" checked>
              <span>Select All / Unselect</span>
            </label>
          </div>

          <div class="table-grid">
            <label class="checkbox-label">
              <input type="checkbox" class="table-checkbox" name="tables[]" value="admin_auth" checked>
              <span>admin_auth</span>
            </label>

            <label class="checkbox-label">
              <input type="checkbox" class="table-checkbox" name="tables[]" value="appointments" checked>
              <span>appointments</span>
            </label>

            <label class="checkbox-label">
              <input type="checkbox" class="table-checkbox" name="tables[]" value="logs" checked>
              <span>logs</span>
            </label>

            <label class="checkbox-label">
              <input type="checkbox" class="table-checkbox" name="tables[]" value="patient_auth" checked>
              <span>patient_auth</span>
            </label>

            <label class="checkbox-label">
              <input type="checkbox" class="table-checkbox" name="tables[]" value="patient_records" checked>
              <span>patient_records</span>
            </label>

            <label class="checkbox-label">
              <input type="checkbox" class="table-checkbox" name="tables[]" value="transactions" checked>
              <span>transactions</span>
            </label>
          </div>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="custom-modal-footer">
        <button type="button" class="btn btn-outline" id="cancelBackupModalBtn">Cancel</button>
        <button type="submit" class="btn btn-primary" id="btnSubmitBackup">Generate Backup</button>
      </div>
    </form>
  </dialog>
</body>
</html>