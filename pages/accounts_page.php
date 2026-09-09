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
    <link rel="stylesheet" href="../styles/add-account-form.css">
    <link rel="stylesheet" href="../styles/toast.css">


    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/add-account.js" defer></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/account-list-loader.js" defer></script>

</head>
<body>

    <div class="top-navbar">
        <div style="display:flex; flex-direction:row; align-items: center;">
            <img src="../images/rhu_logo_topNav.png" alt="rhu_logo2">
            <h3>RHU-Q: Queuing and Patient Management System</h3>
        </div>
        <div class="rightNav">
            <h3>
                <?php
                    $username = $_SESSION['logged_name'];
                    echo ucfirst($username).'!';
                ?>    
            </h3>
        </div>
    </div>

  <div class="workspace">
    
    <div class="sidebar">
        <div class="nav-group">
            <a href="../pages/register_page.php" class="menu-item">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img src="../svg/registration-paper.svg" alt="registration_icon">
                    </div>
                    <span class="menu-text">Registration</span>
                </div>
            </a>
            <div class="dropdown-group">
                <a href="javascript:void(0)" class="menu-item dropdown-toggle" onclick="toggleDropdown(this)">
                    <div class="menu-content">
                        <div class="menu-icon">
                            <img src="../svg/queue-line.svg" alt="queue_icon">
                        </div>
                        <span class="menu-text">Queue</span>
                    </div>
                    <div class="dropdown-arrow">
                        <svg viewBox="0 0 24 24" width="14" height="14">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </a> 
                <div class="submenu-container">
                    <a href="../pages/queue_display_page.php" class="sub-nav-link">
                        <div class="menu-icon">         
                            <img src="../svg/queue-signal.svg" alt="queue_display_icon">
                        </div>
                        <span class="menu-text">Queue Display</span>
                    </a> 
                    
                    <a href="../pages/queue_manager_page.php" class="sub-nav-link">
                        <div class="menu-icon">
                            <img src="../svg/queue-manager.svg" alt="queue-manager-icon">
                        </div>
                        <span class="menu-text">Queue Manager</span> 
                    </a>
                    <a href="../pages/display_settings.php" class="sub-nav-link">
                        <div class="menu-icon">
                            <img src="../svg/display-settings-icon.svg" alt="queue-manager-icon">
                        </div>
                        <span class="menu-text">Edit Display Cards</span> 
                    </a>
                </div>
            </div>

            <a href="../pages/appointment_page.php" class="menu-item">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img src="../svg/reminder-appointment.svg" alt="appointment-icon">
                    </div>
                    <span class="menu-text">Appointment</span>
                    <div class="indicator active">
                        <span class="dot-notification"></span>
                    </div>
                </div>
            </a>
            
            <a href="#" class="menu-item">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img src="../svg/dashboard-monitor.svg" alt="dashboard_icon">
                    </div>
                    <span class="menu-text">Dashboard</span>
                </div>
            </a>
            <a href="#" class="menu-item">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img src="../svg/reports.svg" alt="reports-icon">
                    </div>
                    <span class="menu-text">Reports</span>
                </div>
            </a>

            <a href="../pages/patient_records.php" class="menu-item">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img src="../svg/records.svg" alt="records-icon">
                    </div>
                    <span class="menu-text">Patient Records</span>
                </div>
            </a>
        </div>
        <div class="lowercontrol">
            <a href="../pages/accounts_page.php" class="menu-item account-item active" style="width:200px; padding-left: 0px;">
                <div class="menu-content" style="justify-content: center;">
                    <div class="menu-icon">
                        <img src="../svg/account.svg" alt="account-icon">
                    </div>
                    <span class="menu-text">Account</span>
                </div>
            </a>
            <form action="../php/logout.php" method="post" class="menu-item logout-item" id="logoutForm" style="transform: rotate(180deg);">
                <div class="menu-content">
                    <div class="menu-icon">
                        <button type="submit" name="logout" style="border:none; background:none;">
                            <img src="../svg/logout-icon.svg" alt="logout-icon">
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="content-area">
      <h1 class="page-title">Accounts</h1>

      <div class="content-card">
        <!-- Top Stats & Buttons -->
        <div class="card-top-bar">
          <div class="stats-group">
            <div class="stat-card total">
              <div class="stat-info" >
                <span class="stat-label">Total Accounts</span>
                <?php
                $res = $conn->query("SELECT id FROM admin_auth");
                $total = $res ? $res->num_rows : 0;

                echo '<span class="stat-value" id="stat-card-total">'.$total.'</span>';
                ?>
              </div>
            </div>

            <div class="stat-card active">
              <div class="stat-info">
                <div class="stat-label-container">
                    <span class="stat-label">Active Accounts</span>
                    <span class="pulse-dot" title="Online Status"></span>
                </div>
                <?php
                $res = $conn->query("SELECT COUNT(*) AS Online FROM admin_auth WHERE status = 'Online'");
                $row = $res->fetch_assoc();

                echo '<span class="stat-value" id="stat-card-active">'.$row['Online'].'</span>';
                ?>
              </div>
            </div>
          </div>

          <div class="top-action-button">
            <button class="btn add-account-button" id="open-form-button">
                <img src="../svg/add-account-icon.svg" alt="add-acccount-icon">
            Add Account
            </button>
            <button class="btn btn-secondary"> Backup and Restore
            </button>
            <button class="btn btn-secondary">
              Logs
            </button>
          </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th style="text-align: center; border-top-left-radius:6px;">#</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center;">Role</th>
                <th style="text-align: center;">Created</th>
                <th style="text-align: center;">Last Login</th>
                <th style="text-align: center; border-top-right-radius:6px;">Actions</th>
              </tr>
            </thead>
            <?php
                echo '<tbody class="account-list" id="account-list">';
              
                $res = $conn->query("SELECT id, username, fullname, email, status, role, created_at, last_active FROM admin_auth");

                if($res && $res->num_rows > 0){
                    while ($row = $res->fetch_assoc()){
                    $statusClass = (strtolower($row['status']) === 'online') ? 'badge-online' : 'badge-offline';
                    echo    '<tr>';
                    echo    '<td style="text-align: center;">'.htmlspecialchars($row['id']).'</td>';
                    echo    '<td>'.htmlspecialchars($row['username']).'</td>';
                    echo    '<td>'.htmlspecialchars($row['fullname']).'</td>';
                    echo    '<td>'.htmlspecialchars($row['email']).'</td>';
                    echo '<td style="text-align: center;">';
                    echo '<span class="badge ' . $statusClass . '">' . htmlspecialchars(ucfirst($row['status'])) . '</span>';
                    echo '</td>';
                    echo    '<td style="text-align: center;">'.htmlspecialchars($row['role']).'</td>';
                    echo    '<td style="text-align: center;">'.htmlspecialchars($row['created_at']).'</td>';
                    echo    '<td style="text-align: center;">'.htmlspecialchars($row['last_active']).'</td>';
                    echo    '<td>';
                    echo    '<div class="table-actions">';
                    echo    'P Sample';
                    echo    '</div>';
                    echo    '</td>';
                    echo    '</tr>';
                    }
                  }
              ?>
            </tbody>
          </table>
        </div>
        <dialog id="add-account" class="add-account">
            <div class="toast-container" id="toastContainer"></div>
            <div class="modal-card" id="modal-card">
                <h2>Add Account</h2>
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
            <div class="modal-card" id="modal-card">
                <h2>Edit Account</h2>
                <form id="edit-account-form" method="dialog">
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
                            <button type="button" id="closeBtn" class="btn-cancel" onclick="document.getElementById('edit-account').close()">Cancel</button>
                            <button type="submit" class="btn-submit">Submit</button>
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