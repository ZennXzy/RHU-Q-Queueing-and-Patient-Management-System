<?php
require_once '../api/check-private.php';
require_once '../php/connection.php';

// Authorize ONLY Patients
authorizeRoles(['Patient']);
$currentPage = 'appointments';
$patient_id = $_SESSION['patient_id'] ?? 0;

// Fetch services dynamically from database
$servicesList = [];
$servicesQuery = $conn->query("SELECT service_name FROM services ORDER BY service_id ASC");
if ($servicesQuery) {
    while ($row = $servicesQuery->fetch_assoc()) {
        $servicesList[] = $row['service_name'];
    }
}
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
    <link rel="stylesheet" href="../styles/logout_modal.css">


    <script src="../js/submenu.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/session-check.js" defer></script>
    <script src="../js/patient-appointment.js" defer></script>
    <script src="../js/patient_sidebar.js" defer></script>
    <script src="../js/patient_notification.js" defer></script>

  </head>
  <body>
    <div class="workspace">

      <!-- Main Content Area -->
      <div class="content-area">
        <?php include '../pages/patient_queue_aside.php'; ?>
        <header class="top-bar">
          <div class="toast-container" id="toastContainer"></div>
          <div class="top-bar-left">
              <?php include '../pages/patient_sidebar.php'; ?>
              <h2>My Appointments</h2>
          </div>
        </header>
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
              <select name="service" id="service" class="form-control" required>
                <option value="" disabled selected>-- Select Service --</option>
                <?php foreach ($servicesList as $svc): ?>
                  <option value="<?php echo htmlspecialchars($svc); ?>"><?php echo htmlspecialchars($svc); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="appointment_date">Preferred Date <span class="require_onced">*</span></label>
              <input type="date" id="appointment_date" name="appointment_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
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