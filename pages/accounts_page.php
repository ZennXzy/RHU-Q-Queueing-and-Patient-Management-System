<?php
require '../php/check-private.php';
authorizeRoles(['Admin']);
require '../php/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RHU-Q: Account Manager</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/accounts_page.css">
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
                                <input type="text" id="editUsername" name="username" placeholder="Enter username..." required autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label for="editFullname">Full Name</label>
                                <input type="text" id="editFullname" name="fullname" placeholder="Enter full name..." required autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label for="editEmail">Email Address</label>
                                <input type="email" id="editEmail" name="email" placeholder="Enter email..." required autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label for="editRole">Account Role</label>
                                <select id="editRole" name="role" class="service-type" required>
                                    <option value="" disabled selected>Select Role</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Doctor/Staff">Doctor/Staff</option>
                                    <option value="Display">Display</option>
                                </select>
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
      </div>
    </div>
  </div>

</body>
</html>