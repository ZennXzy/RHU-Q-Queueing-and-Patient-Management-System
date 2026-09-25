<?php
require '../api/check-private.php';
authorizeRoles(['Doctor/Staff']);
require '../php/connection.php';
$role = $_SESSION['logged_role'];
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: Appointments</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/appointment.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/navbar.css">

    <script src="../js/submenu.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/pending-appointment-list.js" defer></script>
    <script src="../js/approved-appointment-list.js" defer></script>
    <script src="../js/session-check.js" defer></script>


  </head>
  <body>
    <?php
        include '../pages/top-navbar.php';
    ?>

    <div class="workspace">
      <?php
          include '../pages/sidebar.php';
      ?>

      <!-- Main Content Area -->
      <div class="content-area">
        <h1 class="page-title">Appointments</h1>

        <div class="topbar">
          <!-- Search Bar -->
          <div class="search-container">
            <div class="search-icon">
              <img src="../svg/search-icon.svg" alt="search-icon">
            </div>
            <input type="text" placeholder="Search by name">
          </div>
          <?php if($role === 'Patient'){?>
          <button type="button" id="addBtn" class="addButton">
            Add Appointment
          </button>
          <?php }?>
        </div>
        <!-- Content Grid -->
        <div class="appointments-grid">
          <div class="pending-card">
            <div class="card-header">
                <h2 class="form-title">Pending Appointment Applications</h2>
                <span class="badge-count" id="pendingCount">0
                </span>
            </div>
            <div class="appointments-list" id="pendingAppointmentsList">
            </div>
          </div>
          <div class="approved-card">
            <div class="card-header">
              <h2 class="form-title">Approved Appointments</h2>
                <span class="badge-count" id="approvedCount">0</span>
            </div>
            <div class="appointments-list" id="approvedAppointmentsList"></div>
          </div>
        </div>
      </div>
    </div>
    <div id="declineModal" class="modal-overlay" style="display: none;">
      <div class="modal-card">
          <div class="modal-header">
              <h3>Request declined</h3>
              <button class="btn-close-modal" id="closeDeclineModal">&times;</button>
          </div>
          <form id="declineForm" action="../php/decline_appointment.php" method="POST">
              <input type="hidden" name="appointment_id" id="declineAppointmentId">
              
              <div class="modal-body">
                  <label for="declineReason">Reason for declining:</label>
                  <textarea id="declineReason" name="reason" rows="4" placeholder="Enter reason here..." required></textarea>
              </div>

              <div class="modal-footer">
                  <button type="button" class="btn-secondary" id="cancelDeclineBtn">Cancel</button>
                  <button type="submit" class="btn-danger">Submit</button>
              </div>
          </form>
      </div>
  </div>
  </body>
</html>

