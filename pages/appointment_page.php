<?php
  require '../php/check-private.php';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RHU-Q: Appointments</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png" />
    <link rel="stylesheet" href="../styles/appointment.css" />
    <link rel="stylesheet" href="../styles/sidebar.css" />
    <link rel="stylesheet" href="../styles/navbar.css" />
    <link rel="stylesheet" href="../styles/appointments-form.css">

    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/add-appointment.js" defer></script>
  </head>
  <body>
    <div class="top-navbar">
      <img src="../images/rhu_logo_topNav.png" alt="rhu_logo2" />
      RHU-Q: Queuing and Patient Management System
    </div>

    <div class="workspace">
      <div class="sidebar">
        <div class="nav-group">
          <a href="../pages/register_page.php" class="menu-item">
            <div class="menu-content">
              <div class="menu-icon">
                <img
                  src="../svg/registration-paper.svg"
                  alt="registration_icon"
                />
              </div>
              <span class="menu-text">Registration</span>
            </div>
          </a>
          <div class="dropdown-group">
            <a
              href="javascript:void(0)"
              class="menu-item dropdown-toggle"
              onclick="toggleDropdown(this)"
            >
              <div class="menu-content">
                <div class="menu-icon">
                  <img src="../svg/queue-line.svg" alt="queue_icon" />
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
                  <img src="../svg/queue-signal.svg" alt="queue_display_icon" />
                </div>
                <span class="menu-text">Queue Display</span>
              </a>

              <a href="../pages/queue_manager_page.php" class="sub-nav-link">
                <div class="menu-icon">
                  <img src="../svg/queue-manager.svg" alt="queue-manager-icon" />
                </div>
                <span class="menu-text">Queue Manager</span>
              </a>
              <a href="../pages/display_settings.php" class="sub-nav-link">
                <div class="menu-icon">
                  <img
                    src="../svg/display-settings-icon.svg"
                    alt="queue-manager-icon"
                  />
                </div>
                <span class="menu-text">Edit Display Cards</span>
              </a>
            </div>
          </div>

          <a href="#" class="menu-item active">
            <div class="menu-content">
              <div class="menu-icon">
                <img
                  src="../svg/reminder-appointment.svg"
                  alt="appointment-icon"
                />
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
                <img src="../svg/dashboard-monitor.svg" alt="dashboard_icon" />
              </div>
              <span class="menu-text">Dashboard</span>
            </div>
          </a>
          <a href="#" class="menu-item">
            <div class="menu-content">
              <div class="menu-icon">
                <img src="../svg/reports.svg" alt="reports-icon" />
              </div>
              <span class="menu-text">Reports</span>
            </div>
          </a>

          <a href="../pages/patient_records.php" class="menu-item">
            <div class="menu-content">
              <div class="menu-icon">
                <img src="../svg/records.svg" alt="records-icon" />
              </div>
              <span class="menu-text">Patient Records</span>
            </div>
          </a>
        </div>
        <div class="lowercontrol">
          <a
            href="../pages/accounts_page.php"
            class="menu-item account-item"
            style="width: 200px; padding-left: 0px"
          >
            <div class="menu-content" style="justify-content: center">
              <div class="menu-icon">
                <img src="../svg/account.svg" alt="account-icon" />
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

      <!-- Main Content Area -->
      <div class="content-area">
        <h1 class="page-title">Appointments</h1>

        <div class="topbar">
          <!-- Search Bar -->
          <div class="search-container">
            <div class="search-icon">
              <img src="../svg/search-icon.svg" alt="search-icon" />
            </div>
            <input type="text" placeholder="Search by name" />
          </div>
          <button type="button" id="addBtn" class="addButton">
            Add Appointment
          </button>
        </div>
        <!-- Content Grid -->
        <div class="appointments-grid">
          <!-- Appointment Form Section -->
          <div class="pending-card">
            <h2 class="form-title">Pending Appointments</h2>

            <form onsubmit="event.preventDefault()">
              <!-- <div class="form-group">
                            <input type="text" class="form-control" placeholder="Full Name">
                        </div>

                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Contact Number">
                        </div>

                        <div class="field-group">
                            <label>Appointment Date</label>
                            <input type="text" value="dd/mm/yyyy">
                        </div>

                        <div class="field-group">
                            <label>Description</label>
                            <textarea placeholder="Type Here..."></textarea>
                        </div>

                        <button type="submit" class="btn-submit">
                            Submit
                        </button> -->
            </form>
          </div>

          <!-- Appointments List Section -->
          <div class="list-card">
            <div class="list-header">
              <img src="../svg/reminder-appointment.svg" alt="appointment-icon" />
              Appointments
            </div>
            <div class="appointment-box">
              <span class="options-menu">⋮</span>

              <div class="patient-info-title">Antonio J Ramos</div>
              <div class="info-row">
                <strong>Contact Number:</strong> 09610586086
              </div>
              <div class="info-row"><strong>Description:</strong></div>
              <div class="desc-text">Follow-up</div>

              <div class="card-footer">
                <div class="appointment-date">Date: 4/16/2026</div>
                <button class="btn-complete">✔ Complete</button>
              </div>
            </div>
          </div>
        </div>

        <div id="form-card" class="form-card">
          <div class="formModal">
            <div class="topbar">
              <h2 class="form-title">Appointment Information</h2>
              <span id="closeBtn" class="closeButton">✕</span>
            </div>

            <form onsubmit="event.preventDefault()">
              <div class="form-group">
                <input
                  type="text"
                  class="form-control"
                  placeholder="Full Name"
                />
              </div>

              <div class="form-group">
                <input
                  type="text"
                  class="form-control"
                  placeholder="Contact Number"
                />
              </div>

              <div class="field-group">
                <label>Appointment Date</label>
                <input type="text" value="dd/mm/yyyy" />
              </div>

              <div class="field-group">
                <label>Description</label>
                <textarea placeholder="Type Here..."></textarea>
              </div>

              <button type="submit" class="btn-submit">Submit</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
