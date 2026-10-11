<?php
require '../api/check-private.php';
authorizeRoles(['Admin']);
require '../php/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RHU-Q: Reports & Analytics</title>
  <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="../styles/reports_page.css">
  <link rel="stylesheet" href="../styles/sidebar.css">
  <link rel="stylesheet" href="../styles/navbar.css">
  <link rel="stylesheet" href="../styles/toast.css">
  <link rel="stylesheet" href="../styles/add-account-form.css">
  <link rel="stylesheet" href="../styles/edit-account-form.css">

  <!-- Script Modules -->
  <script src="../js/submenu.js" defer></script>
  <script src="../js/dot-indicator.js" defer></script>
  <script src="../js/logout.js" defer></script>
  <script src="../js/toast.js" defer></script>
  <script src="../js/session-check.js" defer></script>
  <script src="../js/reports.js" defer></script>
</head>
<body>
    <?php include '../pages/top-navbar.php'; ?>

    <div class="workspace">
        <div class="toast-container" id="toastContainer"></div>
        <?php include '../pages/sidebar.php'; ?>

        <div class="content-area">
            <h1 class="page-title">Reports</h1>

            <div class="content-card">
                <div class="card-top-bar">
                    <div class="left-bar">
                        <!-- Search Bar -->
                        <div class="search-group" style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" id="searchReports" placeholder="Search patient name, token..." class="form-control" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; width: 250px;">
                        </div>
                    </div>
                    <div class="right-bar">
                        <div class="top-action-button" style="display: flex; gap: 10px;">
                            <button class="btn" id="open-filter-button" style="background-color: #4f46e5; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer;">Filter</button>
                            <button class="btn" id="export-btn" style="background-color: #10b981; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer;">Export CSV</button>
                        </div>
                        <div class="stats-group">
                            <div class="stat-info">
                                <span class="stat-label">Total Visits:
                                    <strong class="stat-value" id="stat-total-records">0</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Records Table -->
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: center; border-top-left-radius:6px;">#</th>
                                <th>Date & Time</th>
                                <th>Patient Name</th>
                                <th style="text-align: center;">Age / Gender</th>
                                <th>Service Taken</th>
                                <th>Address / Brgy</th>
                                <th style="text-align: center; border-top-right-radius:6px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reports-table-body">
                            <!-- Populated dynamically via JS -->
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px;">Loading reports...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <dialog id="filter-records-modal" class="edit-account">
        <div class="toast-container" id="toastContainer"></div>
        <div class="modal-card">
            <div class="modal-header">
                <h2>Filter Records</h2>
                <button type="button" class="btn-cancel" id="closeFilterModal" style="border:none; background:none; font-size:1.2rem; cursor:pointer;">&times;</button>
            </div>

            <form id="filter-records-form">
                <div class="formcontainer">
                    <div class="mainform">
                        
                        <!-- Date Range -->
                        <div class="form-group">
                            <label>Date Range</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="date" id="filterDateFrom" name="date_from" class="form-control">
                                <input type="date" id="filterDateTo" name="date_to" class="form-control">
                            </div>
                        </div>

                        <!-- Age Range -->
                        <div class="form-group">
                            <label>Age Range</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="number" id="filterAgeMin" name="age_min" placeholder="Min Age" min="0" max="120" class="form-control">
                                <input type="number" id="filterAgeMax" name="age_max" placeholder="Max Age" min="0" max="120" class="form-control">
                            </div>
                        </div>

                        <!-- Service Taken -->
                        <div class="form-group">
                            <label for="filterService">Service Taken</label>
                            <select id="filterService" name="service" class="service-type" style="width: 100%; padding: 8px; border-radius: 6px;">
                                <option value="">All Services</option>
                                <option value="General Consultation">General Consultation</option>
                                <option value="Dental">Dental</option>
                                <option value="Prenatal">Prenatal</option>
                                <option value="Pediatrics">Pediatrics</option>
                                <option value="Vaccination">Vaccination</option>
                            </select>
                        </div>

                        <!-- Gender -->
                        <div class="form-group">
                            <label for="filterGender">Gender</label>
                            <select id="filterGender" name="gender" class="service-type" style="width: 100%; padding: 8px; border-radius: 6px;">
                                <option value="">All Genders</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- Address -->
                        <div class="form-group">
                            <label for="filterAddress">Address / Barangay</label>
                            <input type="text" id="filterAddress" name="address" placeholder="Enter barangay or address..." autocomplete="off" class="form-control">
                        </div>

                    </div>

                    <div class="form-actions">
                        <button type="reset" id="resetFilterBtn" class="btn-cancel">Reset</button>
                        <button type="submit" class="btn-submit">Apply Filters</button>
                    </div>
                </div>
            </form>
        </div>
    </dialog>

    <!-- View Details Modal -->
    <dialog id="view-record-modal" class="edit-account">
        <div class="modal-card">
            <div class="modal-header">
                <h2>Record Details</h2>
                <button type="button" class="btn-cancel" onclick="document.getElementById('view-record-modal').close()" style="border:none; background:none; font-size:1.2rem; cursor:pointer;">&times;</button>
            </div>
            <div class="formcontainer" style="padding: 20px;">
                <div id="recordDetailsContent">
                    <!-- Loaded dynamically -->
                </div>
                <div class="form-actions" style="margin-top: 20px;">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('view-record-modal').close()">Close</button>
                </div>
            </div>
        </div>
    </dialog>

</body>
</html>