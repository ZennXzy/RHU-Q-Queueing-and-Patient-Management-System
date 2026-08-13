<?php
  require '../php/connection.php';
  require '../php/check-private.php';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: Patient Records</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/patient_records.css">

    <script src="../js/submenu.js" defer></script>
    <script src="../js/logout.js" defer></script>

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
                <img
                  src="../svg/registration-paper.svg"
                  alt="registration_icon"
              >
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
                  <img
                    src="../svg/display-settings-icon.svg"
                    alt="queue-manager-icon"
                >
                </div>
                <span class="menu-text">Edit Display Cards</span>
              </a>
            </div>
          </div>

          <a href="../pages/appointment_page.php" class="menu-item">
            <div class="menu-content">
              <div class="menu-icon">
                <img
                  src="../svg/reminder-appointment.svg"
                  alt="appointment-icon"
              >
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

          <a href="../pages/patient_records.php" class="menu-item active">
            <div class="menu-content">
              <div class="menu-icon">
                <img src="../svg/records.svg" alt="records-icon">
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

      <!-- Main Content Area -->
      <div class="content-area">
        <h1 class="page-title">Patient Records</h1>
        <div class="topbar">
          <!-- Search Bar -->
          <div class="search-container">
            <div class="search-icon">
              <img src="../svg/search-icon.svg" alt="search-icon">
            </div>
            <input type="text" placeholder="Search by name">
          </div>
          <?php
            $total = $_SESSION['total_record'];
            echo '<div class="total-records">';
            echo  '<span>Total Registered Patients: <strong id="totalRecordsCount">'.htmlspecialchars($total).'</strong></span>';
            echo '</div>';
          ?>
        </div>
        <div class="table-card">
          <div class="patient-table">
            <table>
              <thead>
                <div class="staticField" >
                  <tr style="text-align:center;">
                    <th style="text-align:left;">Full Name</th>
                    <th>Age</th>
                    <th>Sex</th>
                    <th>Contact Number</th>
                    <th>Date Registered</th>
                    <th>Actions</th>
                  </tr>
                </div>
              </thead>
              <?php

                $res = $conn->query("SELECT patient_id, firstname, lastname, middlename, suffix, age, gender, contact_number, date_registered FROM patient_records ORDER BY firstname ASC");
                
                

                echo '<div class="patientTableBody" style="overflow:auto;">';
                echo  '<tbody id="patientTableBody" style="text-align:center;">';
                  if($res && $res->num_rows > 0){
                    while ($row = $res->fetch_assoc()){
                      echo '<tr>';
                      echo '<td style="text-align:left;">'.htmlspecialchars($row['firstname']).' '.htmlspecialchars($row['middlename']).' '.htmlspecialchars($row['lastname']).' '.htmlspecialchars($row['suffix']).'</td>';
                      echo '<td>'.htmlspecialchars($row['age']).'</td>';
                      echo '<td>'.htmlspecialchars($row['gender']).'</td>';
                      echo '<td>'.htmlspecialchars($row['contact_number']).'</td>';
                      echo '<td>'.htmlspecialchars($row['date_registered']).'</td>';
                      echo '<td><button class="view-btn" data-id="'.$row['patient_id'].'">View Details</button></td>';
                      echo '</tr>';
                    }
                  } else {
                    echo '<tr>';
                    echo  '<td colspan="5" class="empty-table-placeholder">';
                    echo    '<div class="empty-state">';
                    echo      '<p class="empty-title">No Patient Records Found</p>';
                    echo      '<p class="empty-subtitle">Try adjusting your search criteria or register a new patient.</p>';
                    echo    '</div>';
                    echo  '</td>';
                    echo'</tr>';
                    
                  }
                  $total = $res->num_rows;
                  $_SESSION['total_record'] = $total;
                echo  '</tbody>';
                echo '</div>';
              ?>
            </table>
          </div>

          <dialog id="patientModal" class="patient-modal">
            <div class="modal-header">
              <h2>Patient Details & History</h2>
              <button id="closeModalBtn" class="close-modal-btn">&times;</button>
            </div>
            
            <div class="modal-body">
              <!-- Patient Info Section -->
              <div class="info-section">
                <div class="info-group">
                  <label>Full Name:</label>
                  <span id="modalName">-</span>
                </div>
                <div class="info-group">
                  <label>Age Sex:</label>
                  <span id="modalAgeSex">-</span>
                </div>
                <div class="info-group">
                  <label>Contact Number:</label>
                  <span id="modalContact">-</span>
                </div>
                <div class="info-group">
                  <label>Address:</label>
                  <span id="modalAddress">-</span>
                </div>
              </div>

              <hr class="divider">

              <!-- Service History Section -->
              <div class="history-section">
                <h3>Service History</h3>
                <table class="history-table">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Service</th>
                      <th>Ticket #</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="modalHistoryBody">
                    <!-- Dynamic History Rows -->
                  </tbody>
                </table>
              </div>
            </div>
          </dialog>
        </div>
      </div>
    </div>
  </body>
  <script>
    function attachViewEvents() {
      document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const patientId = e.target.dataset.id;
          const patient = PATIENTS_DATA.find(p => p.id === patientId);

          if (patient) {
            document.getElementById('modalName').textContent = patient.fullName;
            document.getElementById('modalAgeSex').textContent = `${patient.age} / ${patient.sex}`;
            document.getElementById('modalContact').textContent = patient.contact;
            document.getElementById('modalAddress').textContent = patient.address;

            const historyBody = document.getElementById('modalHistoryBody');

            // Service History Empty State Placeholder
            if (!patient.history || patient.history.length === 0) {
              historyBody.innerHTML = `
                <tr>
                  <td colspan="4" style="text-align: center; padding: 20px; color: #777; font-style: italic;">
                    No service history records available for this patient.
                  </td>
                </tr>
              `;
            } else {
              historyBody.innerHTML = patient.history.map(item => `
                <tr>
                  <td>${item.date}</td>
                  <td>${item.service}</td>
                  <td>${item.ticket}</td>
                  <td><span class="status-badge status-${item.status.toLowerCase()}">${item.status}</span></td>
                </tr>
              `).join('');
            }

            modal.showModal();
          }
        });
      });
    }
  </script>
</html>
