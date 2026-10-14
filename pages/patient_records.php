<?php
require_once '../api/check-private.php';
authorizeRoles(['Doctor/Staff']);
require_once '../php/connection.php';
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
    <link rel="stylesheet" href="../styles/toast.css">

  
    <script src="../js/submenu.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/search.js" defer></script>
    <script src="../js/patient-records.js" defer></script>
    <script src="../js/edit-patient.js" defer></script>
    <script src="../js/delete-patient.js" defer></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/session-check.js" defer></script>
    <script src="../js/patient-view-tab.js" defer></script>


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
                  <th>Sex at Birth</th>
                  <th>Contact Number</th>
                  <th>PhilHealth Status</th>
                  <th class="sortable" data-col="4" data-type="date">
                    Date Registered <span class="sort-icon">⇅</span>
                  </th>
                  <th>Actions</th>
                </tr>
              </thead>
              <?php
                $res = $conn->query("SELECT patient_id, firstname, lastname, middlename, suffix, age, gender, contact_number, phil_status, date_registered FROM patient_records ORDER BY firstname ASC");

                echo '<tbody id="patientTableBody" style="text-align:center;">';
                if ($res && $res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td style="text-align:left;">' . htmlspecialchars($row['lastname']) . ', ' . htmlspecialchars($row['firstname']) . ' ' . htmlspecialchars($row['middlename']) . ' ' . htmlspecialchars($row['suffix']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['age']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['gender']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['contact_number']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['phil_status']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['date_registered']) . '</td>';

                        // Action column
                        echo '<td>';
                        if (canManageRecords()) {
                            echo '<button class="edit-btn" data-id="' . htmlspecialchars($row['patient_id']) . '"><img src="../svg/edit-icon.svg" alt="edit-icon"></button>';
                            echo '<button class="view-btn" data-id="' . htmlspecialchars($row['patient_id']) . '"><img src="../svg/view-icon.svg" alt="view-icon"></button>';
                            echo '<button class="delete-btn" data-id="' . htmlspecialchars($row['patient_id']) . '"><img src="../svg/delete-icon.svg" alt="delete-icon"></button>';
                        } else {
                            echo '<span class="text-muted" style="font-size: 0.85rem; color: #9ca3af;">Read Only</span>';
                        }
                        echo '</td>';

                        echo '</tr>';
                    }
                } else {
                    echo '<tr>';
                    echo '<td colspan="7" class="empty-table-placeholder">';
                    echo '<div class="empty-state">';
                    echo '<p class="empty-title">No Patient Records Found</p>';
                    echo '<p class="search-empty-row">Try adjusting your search criteria or register a new patient.</p>';
                    echo '</div>';
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
              ?>
            </table>
          </div>

          <dialog id="patientModal" class="patient-modal">
            <div class="modal-header">
              <div class="header-title">
                <h2>Patient Profile Details</h2>
                <span id="modalPatientIdBadge" class="patient-id-badge">ID: #0000</span>
              </div>
              <div class="modal-tab-group">
                <button type="button" id="btnViewProfile" class="tab-btn active" onclick="switchModalView('profile')">Profile Details</button>
                <button type="button" id="btnViewTransactions" class="tab-btn" onclick="switchModalView('transactions')">View Transactions</button>
              </div>
              <button type="button" id="closeModalBtn" class="close-modal-btn" aria-label="Close" onclick="document.getElementById('patientModal').close()">&times;</button>
            </div>

            <div class="modal-body">
              <div id="patientProfileView" class="patient-profile-grid">
                
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
                    <label>Email Address</label>
                    <span id="modalEmail">-</span>
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
                  <h4 class="card-title">PhilHealth & Yakap</h4>
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
                    <label>Yakap Membership Status</label>
                    <span id="modalYakapMember">-</span>
                  </div>
                </div>
              </div>
              <!-- View 2: Patient Transactions Container -->
              <div id="patientTransactionsView" class="transactions-container" style="display: none;">
                <div id="transactionsList" class="transactions-list">
                  <!-- Dynamic transaction cards will be injected here -->
                </div>
              </div>
            </div>
          </dialog>
          <!-- edit-modal -->
          <dialog class="edit-patient" id="edit-patient">
            <div class="toast-container" id="toastContainer"></div>
            <form id="editPatientForm" method="dialog">
              <div class="modal-header">
                <div class="header-title">
                  <h2>Edit Patient Profile</h2>
                  <!-- <span id="modalPatientIdBadge" class="patient-id-badge">ID: #0000</span> -->
                </div>
                <button type="button" id="closeModalBtn" class="close-modal-btn" aria-label="Close" onclick="document.getElementById('edit-patient').close()">&times;</button>
              </div>

              <div class="modal-body">
                <input type="hidden" id="editPatientId" name="patient_id">

                <div class="edit-patient-profile-grid">
                  
                  <!-- 1. Personal Details -->
                  <div class="info-card">
                    <h4 class="card-title">Personal Information</h4>
                    
                    <div class="info-group">
                      <label for="editFirstName">First Name <span class="require_onced-asterisk">*</span></label>
                      <input type="text" id="editFirstName" name="editFirstName" require_onced placeholder="First Name">
                    </div>
                    <div class="info-group">
                      <label for="editMiddleName">Middle Name</label>
                      <input type="text" id="editMiddleName" name="editMiddleName" placeholder="Middle Name">
                    </div>
                    <div class="info-group">
                      <label for="editLastName">Last Name <span class="require_onced-asterisk">*</span></label>
                      <input type="text" id="editLastName" name="editLastName" require_onced placeholder="Last Name">
                    </div>
                    

                    <div class="info-row grid-3-col">
                      <div class="info-group" style="margin-top:4px;">
                        <label for="editSuffix">Suffix</label>
                        <select name="editSuffix" id="editSuffix">
                            <option value="None">None</option>
                            <option value="Jr.">Jr. (Junior)</option>
                            <option value="Sr.">Sr. (Senior)</option>
                            <option value="I">I</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                            <option value="IV">IV</option>
                        </select>
                      </div>
                      <div class="info-group">
                        <label for="editGender">Gender <span class="require_onced-asterisk">*</span></label>
                        <select id="editGender" name="editGender" require_onced>
                          <option value="">Select Gender</option>
                          <option value="Male">Male</option>
                          <option value="Female">Female</option>
                        </select>
                      </div>
                      <div class="info-group">
                        <label for="editBirthDate">Birth Date <span class="require_onced-asterisk">*</span></label>
                        <input type="date" id="editBirthDate" name="editBirthDate" require_onced>
                      </div>
                    </div>
                    <div class="info-row">
                      <div class="info-group">
                        <label for="editCivilStatus">Civil Status <span class="require_onced-asterisk">*</span></label>
                        <select id="editCivilStatus" name="editCivilStatus">
                          <option value="Single">Single</option>
                          <option value="Married">Married</option>
                          <option value="Widowed">Widowed</option>
                        </select>
                      </div>
                      <div class="info-group">
                        <label for="editReligion">Religion <span class="require_onced-asterisk">*</span></label>
                        <select name="editReligion" id="editReligion">
                            <option value="Roman Catholicism">Roman Catholicism</option>
                            <option value="Islam">Islam</option>
                            <option value="Protestant and Evangelical Groups">Protestant and Evangelical Groups</option>
                            <option value="Iglesia ni Cristo">Iglesia ni Cristo (INC)</option>
                            <option value="Philippine Independent Church (Aglipayan)">Philippine Independent Church (Aglipayan)</option>
                            <option value="Other Groups">Other Groups</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <!-- 2. Contact & Address -->
                  <div class="info-card">
                    <h4 class="card-title">Contact & Location</h4>
                    <div class="info-group">
                      <label for="editContact">Contact Number <span class="require_onced-asterisk">*</span></label>
                      <input type="tel" id="editContact" name="editContact" placeholder="e.g. 09123456789">
                    </div>
                    <div class="info-group">
                      <label for="editAddress">Address <span class="require_onced-asterisk">*</span></label>
                      <input type="text" id="editAddress" name="editAddress" placeholder="Full Address">
                    </div>
                    <div class="info-group">
                      <label for="editDateRegistered">Date Registered</label>
                      <input type="date" id="editDateRegistered" name="editDateRegistered">
                    </div>
                  </div>

                  <!-- 3. Family Background -->
                  <div class="info-card">
                    <h4 class="card-title">Mother's Details</h4>
                    <div class="info-group">
                      <label for="editMotherName">Mother's Maiden Name <span class="require_onced-asterisk">*</span></label>
                      <input type="text" id="editMotherName" name="editMotherName" placeholder="Mother's Full Maiden Name">
                    </div>
                  </div>

                  <div class="info-card">
                    <h4 class="card-title">Father's Details</h4>
                    <div class="info-group">
                      <label for="editFatherName">Father's Name <span class="require_onced-asterisk">*</span></label>
                      <input type="text" id="editFatherName" name="editFatherName" placeholder="Father's Full Name">
                    </div>
                  </div>

                  <!-- Conditional Spouse Field -->
                  <div class="info-card conditional-field" id="modalSpouseGroup">
                    <h4 class="card-title">Spouse's Details</h4>  
                    <div class="info-group">
                      <label for="editSpouseName">Spouse Name</label>
                      <input type="text" id="editSpouseName" name="editSpouseName" placeholder="Spouse Full Name">
                    </div>
                  </div>

                  <!-- 4. Employment Information -->
                  <div class="info-card">
                    <h4 class="card-title">Employment Information</h4>
                    <div class="info-group">
                      <label for="editEmploymentStatus">Employment Status <span class="require_onced-asterisk">*</span></label>
                      <select id="editEmploymentStatus" name="editEmploymentStatus">
                        <option value="Unemployed">Unemployed</option>
                        <option value="Employed">Employed</option>
                        <option value="Self-Employed">Self-Employed</option>
                      </select>
                    </div>
                    <div class="info-group conditional-field" id="modalOccupationGroup">
                      <label for="editOccupation">Occupation</label>
                      <input type="text" id="editOccupation" name="editOccupation" placeholder="Current Occupation">
                    </div>
                    <div class="info-group conditional-field" id="modalBusinessGroup">
                      <label for="editBusiness">Business</label>
                      <input type="text" id="editBusiness" name="editBusiness" placeholder="Business Name / Nature">
                    </div>
                  </div>

                  <!-- 5. Government Program Health Cards -->
                  <div class="info-card">
                    <h4 class="card-title">PhilHealth & Yakap</h4>
                    <div class="info-group">
                      <label for="editPhilStatus">PhilHealth Status <span class="require_onced-asterisk">*</span></label>
                      <select id="editPhilStatus" name="editPhilStatus">
                        <option value="Not Applicable">Not Applicable</option>
                        <option value="Member">Member</option>
                        <option value="Dependent">Dependent</option>
                      </select>
                    </div>
                    <div class="info-group conditional-field" id="modalPhilNumberGroup">
                      <label for="editPhilNumber">PhilHealth Number</label>
                      <input type="text" id="editPhilNumber" name="editPhilNumber" placeholder="XX-XXXXXXXXX-X">
                    </div>
                    <div class="info-group conditional-field" id="modalPrinMemberGroup">
                      <label for="editPrinMember">Principal Member</label>
                      <input type="text" id="editPrinMember" name="editPrinMember" placeholder="Principal Member Name">
                    </div>
                    
                    <hr class="card-inner-divider">
                    
                    <div class="info-group">
                      <label for="editYakapMember">Yakap Membership Status <span class="require_onced-asterisk">*</span></label>
                      <select id="editYakapMember" name="editYakapMember">
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>  

              <!-- Modal Actions Footer -->
              <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="document.getElementById('edit-patient').close()">Cancel</button>
                <button type="submit" class="btn-primary" id="savePatientBtn">Save Changes</button>
              </div>
            </form>
          </dialog>

          <!-- Delete Confirmation Modal -->
          <dialog id="deleteConfirmModal" class="delete-modal">
            <div class="toast-container" id="toastContainer"></div>
            <form id="deletePatientForm" method="dialog">
              <div class="modal-header">
                <h3>Confirm Deletion</h3>
                <button type="button" class="close-modal-btn" onclick="document.getElementById('deleteConfirmModal').close()">&times;</button>
              </div>

              <div class="modal-body">
                <p>Are you sure you want to delete this patient record? This action cannot be undone.</p>
                <input type="hidden" id="deletePatientId" name="patient_id">
                
                <div class="info-group">
                  <label for="deleteAdminPassword">Enter Password to Confirm <span class="require_onced-asterisk">*</span></label>
                  <input type="password" id="deleteAdminPassword" name="admin_password" require_onced placeholder="Enter your password">
                </div>
                <div id="deleteErrorMsg" class="error-message" style="color: #ef4444; margin-top: 8px; display: none;"></div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="document.getElementById('deleteConfirmModal').close()">Cancel</button>
                <button type="submit" class="btn-danger" id="confirmDeleteBtn">Delete Record</button>
              </div>
            </form>
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
