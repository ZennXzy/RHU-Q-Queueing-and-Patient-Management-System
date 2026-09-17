<?php
require '../php/check-private.php';
authorizeRoles(['Doctor/Staff']);
require '../php/connection.php';
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
    <link rel="stylesheet" href="../styles/appointments-form.css">

    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/add-appointment.js" defer></script>
    <script src="../js/logout.js" defer></script>
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
          <button type="button" id="addBtn" class="addButton">
            Add Appointment
          </button>
        </div>
        <!-- Content Grid -->
        <div class="appointments-grid">
          <!-- Appointment Form Section -->
          <div class="pending-card">
            <h2 class="form-title">Pending Appointments Applications</h2>

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
              <img src="../svg/reminder-appointment.svg" alt="appointment-icon">
              Approved Appointments
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
              >
              </div>

              <div class="form-group">
                <input
                  type="text"
                  class="form-control"
                  placeholder="Contact Number"
              >
              </div>

              <div class="field-group">
                <label>Appointment Date</label>
                <input type="text" value="dd/mm/yyyy">
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
