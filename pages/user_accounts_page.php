<?php
require_once '../api/check-private.php';
authorizeRoles(['Admin']);
require_once '../php/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RHU-Q: Account Manager</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/user_accounts.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/add-account-form.css">
    <link rel="stylesheet" href="../styles/toast.css">
    <link rel="stylesheet" href="../styles/logout_modal.css">
    <link rel="stylesheet" href="../styles/edit-account-form.css">


    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/add-account.js" defer></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/edit-account.js" defer></script>
    <script src="../js/session-check.js" defer></script>
    <script src="../js/account-tabs.js" defer></script>

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
        <h1 class="page-title">User Accounts</h1>

        <div class="content-card">
            <div class="card-top-bar">
            <div class="left-bar">
                <div class="table-selector">
                <button class="tab-btn active" data-type="staff">Staff</button>
                <button class="tab-btn" data-type="patient">Patient</button>
                </div>
            </div>
            <div class="right-bar">
                <div class="top-action-button">
                <button class="btn add-account-button" id="open-form-button">Add</button>
                </div>
                <div class="stats-group">
                <div class="stat-info">
                    <span class="stat-label">Total Accounts:
                    <strong class="stat-value" id="stat-card-total">0</strong>
                    </span>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Active Accounts:
                    <strong class="stat-value" id="stat-card-active">0</strong>
                    </span>
                </div>
                </div>
            </div>
            </div>

            <div class="table-responsive">
            <table>
                <thead>
                <tr>
                    <th style="text-align: center; border-top-left-radius:6px;">#</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Token</th>
                    <th style="text-align: center;">Created</th>
                    <th style="text-align: center;">Last Login</th>
                    <th style="text-align: center; border-top-right-radius:6px;">Actions</th>
                </tr>
                </thead>
                <tbody class="account-list" id="account-list">
                <!-- Dynamically loaded via AJAX -->
                </tbody>
            </table>
            </div>
        </div>
        </div>
  </div>
</body>
<dialog id="add-account" class="add-account">
    <div class="toast-container" id="toastContainer"></div>
    <div class="modal-card" id="modal-card">
        <div class="modal-header">
            <h2>Add Account</h2>
        </div>
        <form id="add-account-form" method="dialog">
            <div class="formcontainer">
                <div class="mainform">
                    
                    <div class="form-group" style="position: relative;">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Type suggested username..." autocomplete="off">
                    </div>

                    <div class="form-group" style="position: relative;">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" placeholder="Type your full name..." autocomplete="off">
                    </div>

                    <div class="form-group" style="position: relative;">
                        <label for="email">Email</label>
                        <input type="text" id="email" name="email" placeholder="Type your Email..." autocomplete="off">
                    </div>

                    <div class="form-group" style="position: relative;">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" placeholder="Type your suggested password..." autocomplete="off">
                    </div>

                </div>
                <div class="form-actions">
                    <button type="button" id="closeBtn" class="btn-cancel" onclick="document.getElementById('add-account').close()">Cancel</button>
                    <button type="submit" class="btn-submit">Add</button>
                </div>
            </div>
        </form>
    </div>
</dialog>
<dialog id="edit-account" class="edit-account">
    <div class="toast-container" id="toastContainer"></div>
    <div class="modal-card" id="modal-card">
        <div class="modal-header">
            <h2>Edit Account</h2>
            <span id="modalAccountIdBadge" class="account-badge">ID: #0000</span>
        </div>

        <form id="edit-account-form" method="dialog">
            <input type="hidden" id="editAccountId" name="account_id">

            <div class="formcontainer">
                <div class="mainform">
                    <div class="form-group">
                        <label for="editUsername">Username</label>
                        <input type="text" id="editUsername" name="username" placeholder="Enter username..." require_onced autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="editFullname">Full Name</label>
                        <input type="text" id="editFullname" name="fullname" placeholder="Enter full name..." require_onced autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="editEmail">Email Address</label>
                        <input type="email" id="editEmail" name="email" placeholder="Enter email..." autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="editRole">Account Role</label>
                        <select id="editRole" name="role" class="service-type">
                            <option value="" disabled selected>Select Role</option>
                            <option value="Admin">Admin</option>
                            <option value="Doctor/Staff">Doctor/Staff</option>
                            <option value="Display">Display</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" placeholder="••••••••">
                    </div>

                    <div class="form-group">
                        <label for="editPassword">New Password</label>
                        <input type="password" id="editPassword" name="password" placeholder="Leave blank to keep unchanged" autocomplete="new-password">
                        <small class="field-hint">Only enter if you want to update the password.</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="closeBtn" class="btn-cancel" onclick="document.getElementById('edit-account').close()">Cancel</button>
                    <button type="submit" class="btn-submit">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</dialog>
</html>