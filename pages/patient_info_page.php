<?php
require '../api/check-private.php';
authorizeRoles(['Patient']);
require '../php/connection.php';

$currentPage = 'patient_info'
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: Patient Information</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/patient_sidebar.css">
    <link rel="stylesheet" href="../styles/patient_information.css">
    <link rel="stylesheet" href="../styles/toast.css">

    <script src="../js/patient_sidebar.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/session-check.js" defer></script>
    <script src="../js/patient_information.js" defer></script>
  </head>
  <body>

    <div class="workspace">
      <!-- Main Content Area -->
      <div class="content-area">
        <header class="top-bar">
          <div class="toast-container" id="toastContainer"></div>
          <?php include '../pages/patient_sidebar.php'; ?>
          <h1 class="page-title">Patient Information</h1>
        </header>

        <!-- Embedded Profile Content (No Modal wrappers) -->
        <div class="patient-info-container">

          <div id="patientProfileView" class="patient-profile-grid">
            
            <!-- 1. Personal Details -->
            <div class="info-card">
              <h4 class="card-title">Personal Information</h4>
              <div class="info-group">
                <label>Full Name</label>
                <span id="infoFullName" class="highlight-text">-</span>
              </div>
              <div class="info-row">
                <div class="info-group">
                  <label>Age / Gender</label>
                  <span id="infoAgeGender">-</span>
                </div>
                <div class="info-group">
                  <label>Birth Date</label>
                  <span id="infoBirthDate">-</span>
                </div>
              </div>
              <div class="info-row">
                <div class="info-group">
                  <label>Civil Status</label>
                  <span id="infoCivilStatus">-</span>
                </div>
                <div class="info-group">
                  <label>Religion</label>
                  <span id="infoReligion">-</span>
                </div>
              </div>
            </div>

            <!-- 2. Contact & Address -->
            <div class="info-card">
              <h4 class="card-title">Contact & Location</h4>
              <div class="info-group">
                <label>Contact Number</label>
                <span id="infoContact">-</span>
              </div>
              <div class="info-group">
                <label>Email Address</label>
                <span id="infoEmail">-</span>
              </div>
              <div class="info-group">
                <label>Address</label>
                <span id="infoAddress">-</span>
              </div>
              <div class="info-group">
                <label>Date Registered</label>
                <span id="infoDateRegistered">-</span>
              </div>
            </div>

            <!-- 3. Family Background (Parents & Spouse) -->
            <div class="info-card">
              <h4 class="card-title">Mother's Details</h4>
              <div class="info-group">
                <label>Mother's Maiden Name</label>
                <span id="infoMotherName">-</span>
              </div>
            </div>

            <div class="info-card">
              <h4 class="card-title">Father's Details</h4>
              <div class="info-group">
                <label>Father's Name</label>
                <span id="infoFatherName">-</span>
              </div>
            </div>

            <div class="info-card conditional-field" id="infoSpouseGroup" style="display: none;">
              <h4 class="card-title">Spouse's Details</h4>  
              <div class="info-group">
                <label>Spouse Name</label>
                <span id="infoSpouseName">-</span>
              </div>
            </div>

            <!-- 4. Employment Information -->
            <div class="info-card">
              <h4 class="card-title">Employment Information</h4>
              <div class="info-group">
                <label>Employment Status</label>
                <span id="infoEmploymentStatus">-</span>
              </div>
              <div class="info-group conditional-field" id="infoOccupationGroup" style="display: none;">
                <label>Occupation</label>
                <span id="infoOccupation">-</span>
              </div>
              <div class="info-group conditional-field" id="infoBusinessGroup" style="display: none;">
                <label>Business</label>
                <span id="infoBusiness">-</span>
              </div>
            </div>

            <!-- 5. Government Program Health Cards -->
            <div class="info-card">
              <h4 class="card-title">PhilHealth & Yakap</h4>
              <div class="info-group">
                <label>PhilHealth Status</label>
                <span id="infoPhilStatus">-</span>
              </div>
              <div class="info-group conditional-field" id="infoPhilNumberGroup" style="display: none;">
                <label>PhilHealth Number</label>
                <span id="infoPhilNumber">-</span>
              </div>
              <div class="info-group conditional-field" id="infoPrinMemberGroup" style="display: none;">
                <label>Principal Member</label>
                <span id="infoPrinMember">-</span>
              </div>
              
              <hr class="card-inner-divider">
              
              <div class="info-group">
                <label>Yakap Membership Status</label>
                <span id="infoYakapMember">-</span>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </body>
</html>