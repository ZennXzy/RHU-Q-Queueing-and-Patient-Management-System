<?php
require_once '../api/check-private.php';
authorizeRoles(['Admin', 'Doctor/Staff']);
require_once '../php/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RHU-Q: Account Manager</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/account-settings.css">
    <link rel="stylesheet" href="../styles/account-settings-modal.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/toast.css">
    <link rel="stylesheet" href="../styles/edit-account-form.css">
    <link rel="stylesheet" href="../styles/logs_modal.css">
    <link rel="stylesheet" href="../styles/logout_modal.css">
    <link rel="stylesheet" href="../styles/service-modal.css">
    <link rel="stylesheet" href="../styles/delete-service-modal.css">

    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/edit-account.js" defer></script>
    <script src="../js/backup_modal.js" defer></script>
    <script src="../js/session-check.js" defer></script>
    <script src="../js/logs-modal.js" defer></script>
    <script src="../js/services-manager.js" defer></script>
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
            <button class="btn btn-secondary" id="open-logs-button" type="button">Logs</button>
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
              <input type="text" id="editUsername" name="username" class="form-control" placeholder="Enter username" require_onced>
            </div>

            <!-- Full Name Field -->
            <div class="form-group">
              <label for="editFullname">Full Name</label>
              <input type="text" id="editFullname" name="fullname" class="form-control" placeholder="Enter full name" require_onced>
            </div>

            <!-- Email Field -->
            <div class="form-group">
              <label for="editEmail">Email Address</label>
              <input type="email" id="editEmail" name="email" class="form-control" placeholder="name@example.com" require_onced>
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

      <?php if($_SESSION['logged_role'] === 'Admin'){ ?>
      <!-- SERVICES-CARD: Manage Services (Add, Edit, Delete) -->
      <div class="account-card" id="services-card" style="margin-top: 30px;">
        <div class="card-top-bar">
          <div class="account-header">
            <h2 class="account-title">Manage Services</h2>
            <span class="badge-role" style="background-color: #0B695D; color: #fff;">Catalog</span>
          </div>
          <div class="top-action-button">
            <!-- Mobile Toggle Button -->
            <button type="button" class="btn btn-secondary" id="toggleServicesBtn">Show Services</button>
            
            <?php if(($_SESSION['logged_role'] ?? '') === "Admin"){ ?>
            <button type="button" class="btn btn-primary" id="openAddServiceModalBtn" style="background-color: #0B695D; color: #fff; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer;">
              + Add New Service
            </button>
            <?php } ?>
          </div>
        </div>

        <hr class="divider" id="servicesDivider">

        <!-- Collapsable Services Body -->
        <div class="services-card-body" id="servicesCardBody">
          <div class="table-responsive" style="overflow-x: auto; margin-top: 15px;">
            <table class="logs-table" style="width: 100%; border-collapse: collapse; text-align: left;">
              <thead>
                <tr style="border-bottom: 2px solid #e2e8f0; background-color: #f8fafc;">
                  <th style="padding: 12px; font-weight: 600; color: #475569;">ID</th>
                  <th style="padding: 12px; font-weight: 600; color: #475569;">Service Name</th>
                  <th style="padding: 12px; font-weight: 600; color: #475569;">Status</th>
                  <th style="padding: 12px; font-weight: 600; color: #475569;">Created At</th>
                  <?php if(($_SESSION['logged_role'] ?? '') === "Admin"){ ?>
                  <th style="padding: 12px; font-weight: 600; color: #475569; text-align: right;">Actions</th>
                  <?php } ?>
                </tr>
              </thead>
              <tbody id="servicesTableBody">
                <tr>
                  <td colspan="4" style="padding: 20px; text-align: center; color: #64748b;">Loading services...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php }?>
    </div>
  </div>
  <dialog id="serviceModal" class="service-modal" aria-labelledby="serviceModalTitle">
      <!-- Modal Header -->
      <div class="service-modal__header">
          <div class="service-modal__header-info">
              <div class="service-modal__icon">
                  <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="22"
                      height="22"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      aria-hidden="true"
                  >
                      <rect x="3" y="3" width="7" height="7" rx="1"/>
                      <rect x="14" y="3" width="7" height="7" rx="1"/>
                      <rect x="3" y="14" width="7" height="7" rx="1"/>
                      <path d="M14 17.5h7M17.5 14v7"/>
                  </svg>
              </div>
              <div class="service-modal__heading">
                  <h2
                      id="serviceModalTitle"
                      class="service-modal__title"
                  >
                      Add New Service
                  </h2>
                  <p class="service-modal__subtitle">
                      Manage services available in RHU-Q.
                  </p>
              </div>
          </div>
          <!-- Close Button -->
          <button type="button" id="closeServiceModalBtn" class="service-modal__close" aria-label="Close service modal">
              <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="18"
                  height="18"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  aria-hidden="true"
              >
                  <path d="M18 6 6 18M6 6l12 12"/>
              </svg>
          </button>
      </div>
      <!-- Modal Form -->
      <form id="serviceForm" class="service-modal__form">
          <!-- Hidden Service ID -->
          <input type="hidden" id="serviceId" name="service_id">
          <!-- Modal Body -->
          <div class="service-modal__body">
              <div class="service-modal__field">
                  <label for="serviceNameInput" class="service-modal__label">
                      Service Name
                      <span class="service-modal__required">*</span>
                  </label>
                  <input type="text" id="serviceNameInput" name="service_name" class="service-modal__input" placeholder="e.g., General Consultation" autocomplete="off" maxlength="150" required>
                  <p class="service-modal__hint">
                      Enter a clear and descriptive service name.
                  </p>
              </div>
          </div>
          <!-- Modal Footer -->
          <div class="service-modal__footer">
              <button type="button" id="cancelServiceModalBtn" class="service-modal__cancel">
                  Cancel
              </button>
              <button type="submit" id="saveServiceBtn" class="service-modal__submit">
                  <span>Save Service</span>
              </button>
          </div>
      </form>
  </dialog>

<dialog
    id="deleteServiceModal"
    class="delete-service-modal"
    aria-labelledby="deleteServiceModalTitle"
    aria-describedby="deleteServiceDescription"
>
    <!-- Header -->
    <div class="delete-service-modal__header">

        <div class="delete-service-modal__header-info">

            <div class="delete-service-modal__icon">
                <svg viewBox="0 0 24 24" width="22" height="22"
                     fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 6h18"/>
                    <path d="M8 6V4h8v2"/>
                    <path d="M5 6l1 14h12l1-14"/>
                    <path d="M10 10v6M14 10v6"/>
                </svg>
            </div>

            <div>
                <h2 id="deleteServiceModalTitle"
                    class="delete-service-modal__title">
                    Delete Service
                </h2>

                <p class="delete-service-modal__subtitle">
                    Confirm service deletion
                </p>
            </div>
        </div>

        <button
            type="button"
            id="closeDeleteServiceModalBtn"
            class="delete-service-modal__close"
            aria-label="Close delete service modal"
        >
            &times;
        </button>

    </div>

    <!-- Form -->
    <form id="deleteServiceForm" class="delete-service-modal__form">

        <input type="hidden"
               id="deleteServiceId"
               name="service_id">

        <!-- Body -->
        <div class="delete-service-modal__body">

            <p id="deleteServiceDescription"
               class="delete-service-modal__message">
                Are you sure you want to delete this service?
            </p>

            <div class="delete-service-modal__service-info">
                <span class="delete-service-modal__info-label">
                    Selected Service
                </span>

                <strong id="deleteServiceName">
                    —
                </strong>
            </div>

            <div class="delete-service-modal__warning">
                <svg viewBox="0 0 24 24" width="18" height="18"
                     fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round" aria-hidden="true">
                    <path d="m10.3 3.5-8.5 14.7A2 2 0 0 0 3.5 21h17a2 2 0 0 0 1.7-2.8L13.7 3.5a2 2 0 0 0-3.4 0Z"/>
                    <path d="M12 9v4M12 17h.01"/>
                </svg>

                <span>
                    This action cannot be undone. Please verify
                    the service before proceeding.
                </span>
            </div>

        </div>

        <!-- Footer -->
        <div class="delete-service-modal__footer">

            <button type="button"
                    id="cancelDeleteServiceBtn"
                    class="delete-service-modal__cancel">
                Cancel
            </button>

            <button type="submit"
                    id="confirmDeleteServiceBtn"
                    class="delete-service-modal__submit">
                <svg viewBox="0 0 24 24" width="17" height="17"
                     fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 6h18M8 6V4h8v2M5 6l1 14h12l1-14"/>
                </svg>
                <span>Delete Service</span>
            </button>

        </div>
    </form>
</dialog>

  <!-- backup/restore modal -->
   <dialog class="backupModal" id="backupModal">
    <!-- Modal Header -->
    <div class="custom-modal-header">
      <h5 class="custom-modal-title">DATABASE BACKUP</h5>
      <button type="button" class="custom-modal-close" id="closeBackupModalBtn">&times;</button>
    </div>

    <!-- Modal Body / Form -->
    <form action="../php/backup.php" method="POST" id="backupForm" class="backupForm">
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

            <label class="checkbox-label">
              <input type="checkbox" class="table-checkbox" name="tables[]" value="services" checked>
              <span>services</span>
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

 <!-- Logs modal -->
<dialog id="logsModal" class="logs-modal" aria-labelledby="logsTitle">
    <div class="logs-modal__header">
        <h2 class="logs-modal__title" id="logsTitle">Activity Logs</h2>
        <button type="button" class="logs-modal__close" id="logsCloseBtn" aria-label="Close logs">&times;</button>
    </div>

    <div class="logs-modal__toolbar">
        <input type="search" id="logsSearch" class="logs-modal__search" placeholder="Search logs..." autocomplete="off" aria-label="Search logs">
        <button type="button" class="logs-btn" id="logsRefreshBtn">Refresh</button>
    </div>

    <div class="logs-modal__body">
        <table class="logs-table">
            <thead>
                <tr id="logsHeadRow"></tr>
            </thead>
            <tbody id="logsBody">
                <tr class="logs-state"><td>Loading logs...</td></tr>
            </tbody>
        </table>
    </div>

    <div class="logs-modal__footer">
        <span class="logs-modal__info" id="logsInfo" aria-live="polite"></span>
        <div class="logs-modal__pager">
            <button type="button" class="logs-btn" id="logsPrevBtn" disabled>Previous</button>
            <span class="logs-modal__page" id="logsPageLabel"></span>
            <button type="button" class="logs-btn logs-btn--primary" id="logsNextBtn" disabled>Next</button>
        </div>
    </div>
</dialog>
</body>
</html>