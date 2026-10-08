<?php
require_once_once '../api/check-private.php';
require_once_once '../php/connection.php';

// Authorize ONLY Patients
authorizeRoles(['Patient']);
$currentPage = 'appointments';
$patient_id = $_SESSION['patient_id'] ?? 0;
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: My Appointments</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/appointment.css">
    <link rel="stylesheet" href="../styles/patient_sidebar.css">

    <script src="../js/submenu.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/session-check.js" defer></script>
    <script src="../js/patient-appointment.js" defer></script>
    <script src="../js/patient_sidebar.js" defer></script>
  </head>
  <body>
    <div class="workspace">

      <!-- Main Content Area -->
      <div class="content-area">
        <div class="topbar">
          <?php include '../pages/patient_sidebar.php'; ?>
          <h1 class="page-title">My Appointments</h1>
        </div>
        <div class="action-button">
          <button type="button" id="addBtn" class="addButton">
            Book Appointment
          </button>
        </div>
        <!-- Single Column or Grid Layout for Patient's Appointments -->
        <div class="pending-card" style="width: 100%;">
          <div class="card-header">
            <h2 class="form-title">My Appointments</h2>
            <span class="badge-count" id="patientAppCount">0</span>
          </div>
          <div class="appointments-list" id="patientAppointmentsList">
            <!-- Dynamic content loaded via JS -->
          </div>
        </div>
      </div>
    </div>

    <!-- Book Appointment Modal -->
    <div id="addAppointmentModal" class="modal-overlay" style="display: none;">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Schedule New Appointment</h3>
          <button class="btn-close-modal" id="closeAddModal">&times;</button>
        </div>
        <form id="addAppointmentForm" action="../php/patient_add_appointment.php" method="POST">
          <div class="modal-body">
            <div class="form-group">
              <label for="service">Select Service <span class="require_onced">*</span></label>
              <select name="service" id="service" class="form-control" require_onced>
                <option value="" disabled selected>-- Select Service --</option>
                <option value="Medical Consultation">Medical Consultation</option>
                <option value="Immunization">Immunization</option>
                <option value="Pre-Natal">Pre-Natal</option>
                <option value="Medical Laboratory">Medical Laboratory</option>
                <option value="Family Planning">Family Planning</option>
                <option value="Dental Examination/Tooth Extraction">Dental Examination/Tooth Extraction</option>
                <option value="Availment of anti-Tuberculosis Drugs">Availment of anti-Tuberculosis Drugs</option>
                <option value="Child Care services">Child Care services</option>
                <option value="Availment of Tetanus Vaccine and Anti Rabies Vaccine">Availment of Tetanus Vaccine and Anti Rabies Vaccine</option>
              </select>
            </div>

            <div class="form-group">
              <label for="appointment_date">Preferred Date <span class="require_onced">*</span></label>
              <input type="date" id="appointment_date" name="appointment_date" class="form-control" min="<?= date('Y-m-d') ?>" require_onced>
            </div>

            <div class="form-group">
              <label for="remarks">Remarks / Reason for Visit</label>
              <textarea id="remarks" name="remarks" class="form-control" rows="3" placeholder="Briefly state your health concern..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-secondary" id="cancelAddBtn">Cancel</button>
            <button type="submit" class="btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>