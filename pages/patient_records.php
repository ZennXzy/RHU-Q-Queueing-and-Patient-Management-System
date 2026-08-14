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
    <!-- <link rel="stylesheet" href="../styles/patient_records.css"> -->

    <link rel="stylesheet" href="../styles/patient_records.css?v=<?php echo time(); ?>">

    <script src="../js/submenu.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/search.js" defer></script>
    <script src="../js/patient-records.js" defer></script>

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
            <input type="text" id="patientSearchInput" placeholder="Search by name">
          </div>
          <?php
            $res = $conn->query("SELECT patient_id FROM patient_records");
            $total = $res ? $res->num_rows : 0;
            echo '<div class="total-records">';
            echo  '<span>Total Registered Patients: <strong id="totalRecordsCount">'.htmlspecialchars($total).'</strong></span>';
            echo '</div>';
          ?>
        </div>
        <div class="table-card">
          <div class="patient-table">
            <table>
              <thead>
                <tr style="text-align:center;">
                  <th style="text-align:left;" class="sortable" data-col="0" data-type="text">
                    Full Name <span class="sort-icon">⇅</span>
                  </th>
                  <th class="sortable" data-col="1" data-type="number">
                    Age <span class="sort-icon">⇅</span>
                  </th>
                  <th>Sex</th>
                  <th>Contact Number</th>
                  <th class="sortable" data-col="4" data-type="date">
                    Date Registered <span class="sort-icon">⇅</span>
                  </th>
                  <th>Actions</th>
                </tr>
              </thead>
              <?php
                $res = $conn->query("SELECT patient_id, firstname, lastname, middlename, suffix, age, gender, contact_number, date_registered FROM patient_records ORDER BY firstname ASC");

                echo  '<tbody id="patientTableBody" style="text-align:center;">';
                  if($res && $res->num_rows > 0){
                    while ($row = $res->fetch_assoc()){
                      echo '<tr>';
                      echo '<td style="text-align:left;">'.htmlspecialchars($row['firstname']).' '.htmlspecialchars($row['middlename']).' '.htmlspecialchars($row['lastname']).' '.htmlspecialchars($row['suffix']).'</td>';
                      echo '<td>'.htmlspecialchars($row['age']).'</td>';
                      echo '<td>'.htmlspecialchars($row['gender']).'</td>';
                      echo '<td>'.htmlspecialchars($row['contact_number']).'</td>';
                      echo '<td>'.htmlspecialchars($row['date_registered']).'</td>';
                      echo '<td><button class="view-btn" data-id="' . htmlspecialchars($row['patient_id']) . '">View Details</button></td>';
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
                echo  '</tbody>';
                ?>
            </table>
          </div>

          <dialog id="patientModal" class="patient-modal">
            <div class="modal-header">
              <div class="header-title">
                <h2>Patient Profile Details</h2>
                <span id="modalPatientIdBadge" class="patient-id-badge">ID: #0000</span>
              </div>
              <button type="button" id="closeModalBtn" class="close-modal-btn" aria-label="Close" onclick="document.getElementById('patientModal').close()">&times;</button>
            </div>

            <div class="modal-body">
              <div class="patient-profile-grid">
                
                <!-- 1. Personal Details -->
                <div class="info-card">
                  <h4 class="card-title">Personal Information</h4>
                  <div class="info-group">
                    <label>Full Name</label>
                    <span id="modalFullName" class="highlight-text">-</span>
                  </div>
                  <div class="info-row">
                    <div class="info-group">
                      <label>Age / Gender</label>
                      <span id="modalAgeGender">-</span>
                    </div>
                    <div class="info-group">
                      <label>Birth Date</label>
                      <span id="modalBirthDate">-</span>
                    </div>
                  </div>
                  <div class="info-row">
                    <div class="info-group">
                      <label>Civil Status</label>
                      <span id="modalCivilStatus">-</span>
                    </div>
                    <div class="info-group">
                      <label>Religion</label>
                      <span id="modalReligion">-</span>
                    </div>
                  </div>
                </div>

                <!-- 2. Contact & Address -->
                <div class="info-card">
                  <h4 class="card-title">Contact & Location</h4>
                  <div class="info-group">
                    <label>Contact Number</label>
                    <span id="modalContact">-</span>
                  </div>
                  <div class="info-group">
                    <label>Address</label>
                    <span id="modalAddress">-</span>
                  </div>
                  <div class="info-group">
                    <label>Date Registered</label>
                    <span id="modalDateRegistered">-</span>
                  </div>
                </div>

                <!-- 3. Family Background (Parents) -->
                <div class="info-card">
                  <h4 class="card-title">Mother's Details</h4>
                  <div class="info-group">
                    <label>Mother's Maiden Name</label>
                    <span id="modalMotherName">-</span>
                  </div>
                </div>

                <div class="info-card">
                  <h4 class="card-title">Father's Details</h4>
                  <div class="info-group">
                    <label>Father's Name</label>
                    <span id="modalFatherName">-</span>
                  </div>
                </div>

                <!-- Conditional Spouse Field -->
                <div class="info-card conditional-field" id="modalSpouseGroup" style="display: none;">
                  <h4 class="card-title">Spouse's Details</h4>  
                  <div class="info-group">
                    <label>Spouse Name</label>
                    <span id="modalSpouseName">-</span>
                  </div>
                </div>

                <!-- 4. Employment Information -->
                <div class="info-card">
                  <h4 class="card-title">Employment Information</h4>
                  <div class="info-group">
                    <label>Employment Status</label>
                    <span id="modalEmploymentStatus">-</span>
                  </div>
                  <div class="info-group conditional-field" id="modalOccupationGroup">
                    <label>Occupation</label>
                    <span id="modalOccupation">-</span>
                  </div>
                  <div class="info-group conditional-field" id="modalBusinessGroup">
                    <label>Business</label>
                    <span id="modalBusiness">-</span>
                  </div>
                </div>

                <!-- 5. Government Program Health Cards -->
                <div class="info-card">
                  <h4 class="card-title">PhilHealth & NHTS</h4>
                  <div class="info-group">
                    <label>PhilHealth Status</label>
                    <span id="modalPhilStatus">-</span>
                  </div>
                  <div class="info-group conditional-field" id="modalPhilNumberGroup">
                    <label>PhilHealth Number</label>
                    <span id="modalPhilNumber">-</span>
                  </div>
                  <div class="info-group conditional-field" id="modalPrinMemberGroup">
                    <label>Principal Member</label>
                    <span id="modalPrinMember">-</span>
                  </div>
                  
                  <hr class="card-inner-divider">
                  
                  <div class="info-group">
                    <label>NHTS Status</label>
                    <span id="modalNhtsStatus">-</span>
                  </div>
                  <div class="info-group conditional-field" id="modalNhtsNumberGroup">
                    <label>NHTS Number</label>
                    <span id="modalNhtsNumber">-</span>
                  </div>
                </div>

              </div>

              <hr class="divider">

              <!-- Service & Queue History -->
              <div class="history-section">
                <h4 class="card-title">Service & Queue History</h4>
                <div class="history-table-container">
                  <table class="history-table">
                    <thead>
                      <tr>
                        <th>Date & Time</th>
                        <th>Service Rendered</th>
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
            </div>
          </dialog>
        </div>
      </div>
    </div>
  </body>
  <!-- sort function -->
   <script>
    document.addEventListener('DOMContentLoaded', () => {
      const tableBody = document.getElementById('patientTableBody');
      const sortableHeaders = document.querySelectorAll('th.sortable');

      // Track sort directions: 0 = Full Name, 1 = Age, 4 = Date Registered
      const sortDirections = { 0: 'asc', 1: 'asc', 4: 'asc' };

      sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';

        header.addEventListener('click', () => {
          const colIndex = parseInt(header.dataset.col, 10);
          const dataType = header.dataset.type;
          const currentDir = sortDirections[colIndex];
          const isAscending = currentDir === 'asc';

          // Reset all sort icons
          sortableHeaders.forEach(h => {
            const icon = h.querySelector('.sort-icon');
            if (icon) icon.textContent = '⇅';
          });

          // Update active icon arrow
          const icon = header.querySelector('.sort-icon');
          if (icon) icon.textContent = isAscending ? '▲' : '▼';

          // Get table rows (excluding empty state placeholders)
          const rows = Array.from(tableBody.querySelectorAll('tr')).filter(
            row => !row.querySelector('.empty-table-placeholder') && !row.classList.contains('search-empty-row')
          );

          rows.sort((rowA, rowB) => {
            const cellA = rowA.children[colIndex].textContent.trim();
            const cellB = rowB.children[colIndex].textContent.trim();

            let valA, valB;

            if (dataType === 'number') {
              valA = parseFloat(cellA) || 0;
              valB = parseFloat(cellB) || 0;
            } else if (dataType === 'date') {
              valA = new Date(cellA).getTime() || 0;
              valB = new Date(cellB).getTime() || 0;
            } else {
              valA = cellA.toLowerCase();
              valB = cellB.toLowerCase();
            }

            if (valA < valB) return isAscending ? -1 : 1;
            if (valA > valB) return isAscending ? 1 : -1;
            return 0;
          });

          // Toggle direction state for next click
          sortDirections[colIndex] = isAscending ? 'desc' : 'asc';

          // Re-append rows in new order
          rows.forEach(row => tableBody.appendChild(row));
        });
      });
    });
   </script>
</html>
