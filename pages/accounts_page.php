<?php
  require '../php/check-private.php';
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


    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
</head>
<body>

    <div class="top-navbar">
        <img src="../images/rhu_logo_topNav.png" alt="rhu_logo2">
        RHU-Q: Queuing and Patient Management System
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
            <form action="../php/logout.php" class="menu-item logout-item" id="logoutForm" style="transform: rotate(180deg);">
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
              <i class="fa-solid fa-users stat-icon"></i>
              <div class="stat-info">
                <span class="stat-label">Total Accounts</span>
                <span class="stat-value">1</span>
              </div>
            </div>

            <div class="stat-card active">
              <i class="fa-solid fa-user-check stat-icon"></i>
              <div class="stat-info">
                <span class="stat-label">Active Accounts</span>
                <span class="stat-value">1</span>
              </div>
            </div>
          </div>

          <div class="action-buttons">
            <button class="btn btn-primary">
              <i class="fa-solid fa-plus"></i> Add Account
            </button>
            <button class="btn btn-secondary">
              <i class="fa-solid fa-database"></i> Backup and Restore
            </button>
          </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Created</th>
                <th>Last Login</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>admin</td>
                <td>Administrator</td>
                <td>admin@rhu.gov.ph</td>
                <td><span class="badge badge-active">Active</span></td>
                <td>5/11/2026</td>
                <td>5/11/2026</td>
                <td>
                  <div class="table-actions">
                    <i class="fa-solid fa-pen action-icon"></i>
                    <i class="fa-solid fa-trash action-icon delete"></i>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Floating Bottom-Right Logout Button -->
        <button class="btn btn-logout">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
      </div>
    </div>
  </div>

</body>
</html>