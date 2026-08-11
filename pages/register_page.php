<?php
  require '../php/connection.php';
  require '../php/check-private.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: Patient Registration</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/register_page.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/toast.css">


    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/register-selector.js" defer></script>
    <script src="../js/toast.js" defer></script>
</head>
<body>

    <div class="top-navbar">
        <img src="../images/rhu_logo_topNav.png" alt="rhu_logo2">
        RHU-Q: Queuing and Patient Management System
    </div>

    <div class="workspace">
        <div class="toast-container" id="toastContainer"></div>
        <div class="sidebar">
            <div class="nav-group">
                <a href="../pages/register_page.php" class="menu-item active">
                    <div class="menu-content">
                        <div class="menu-icon">
                            <img src="../svg/registration-paper.svg" alt="registration_icon">
                        </div>
                        <span class="menu-text">Registration</span>
                    </div>
                </a>
                <div class="dropdown-group">
                    <a href="javascript:void(0)" class="menu-item dropdown-toggle" onclick="toggleDropdown(this)">
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
                                <img src="../svg/display-settings-icon.svg" alt="queue-manager-icon">
                            </div>
                            <span class="menu-text">Edit Display Cards</span> 
                        </a>
                    </div>
                </div>

                <a href="../pages/appointment_page.php" class="menu-item">
                    <div class="menu-content">
                        <div class="menu-icon">
                            <img src="../svg/reminder-appointment.svg" alt="appointment-icon">
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

                <a href="../pages/patient_records.php" class="menu-item">
                    <div class="menu-content">
                        <div class="menu-icon">
                            <img src="../svg/records.svg" alt="records-icon">
                        </div>
                        <span class="menu-text">Patient Records</span>
                    </div>
                </a>
            </div>
            <div class="lowercontrol">
                <a href="../pages/accounts_page.php" class="menu-item account-item" style="width:200px; padding-left: 0px;">
                    <div class="menu-content" style="justify-content: center;">
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
        

        <!-- main container -->
        <form action="" class="content-area">
            <h2 class="page-title">Patient Registration/Clinical Form</h2>
            <h3 class="section-title">Personal Information</h3>
            <div class="white-card">
                <div class="form-grid">    
                    <div class="form-group col-4">
                        <input type="text" placeholder="First Name">
                    </div>
                    <div class="form-group col-4">
                        <input type="text" placeholder="Last Name">
                    </div>
                    <div class="form-group col-4">
                        <input type="text" placeholder="Middle Name">
                    </div>

                    <div class="form-group col-7">
                        <input type="text" placeholder="Complete Address">
                    </div>
                    <div class="form-group col-2">
                        <input type="text" placeholder="Age">
                    </div>
                    <div class="form-group col-3" style="padding-left: 10px;">
                        <span style="font-size: 13px; color: #555;">Sex</span>
                        <div class="checkbox-row">
                            <label class="checkbox-item"><input type="checkbox" class="gender-checkbox" value="male"> Male</label>
                            <label class="checkbox-item" style="margin-left: 25px;"><input type="checkbox" class="gender-checkbox" value="female"> Female</label>
                        </div>
                    </div>

                    <div class="form-group col-4">
                        <span style="font-size: 13px; color: #555;">Date of Birth</span>
                        <input type="text" placeholder="dd/mm/yyyy" style="margin-top:10px;">
                    </div>
                    <div class="form-group col-4">
                        <span style="font-size: 13px; color: #555;">Contact Number</span>
                        <input type="text" placeholder="09-123-456-7890" style="margin-top:10px;">
                    </div>
                    <div class="form-group col-4" style="grid-row: span 2; justify-content: flex-start;">
                        <span style="font-size: 13px; color: #555;">Employment Status</span>

                        <!-- Radio Input Group -->
                        <div class="radio-row" style="margin-top: 8px;">
                            <label class="radiobox-item">
                                <input type="radio" name="employment_status" class="employment-status employed" value="employed" onclick="handleEmploymentChange()"> Employed 
                            </label>
                            <label class="radiobox-item">
                                <input type="radio" name="employment_status" class="employment-status self-employed" value="self-employed" onclick="handleEmploymentChange()"> Self-Employed
                            </label>
                            <label class="radiobox-item">
                                <input type="radio" name="employment_status" class="employment-status unemployed" value="unemployed" onclick="handleEmploymentChange()"> Unemployed
                            </label>
                        </div>

                        <!-- Text Inputs (Initially hidden inline) -->
                        <div class="fieldset-group col-5 occupation-item" id="occupationDiv" style="margin-top: 20px; display: none;">
                            <legend>Occupation</legend>
                            <input type="text" name="occupation" id="occupationInput" placeholder="e.g., Software Engineer">
                        </div>

                        <div class="fieldset-group col-5 business-item" id="businessDiv" style="margin-top: 20px; display: none;">
                            <legend>Business</legend>
                            <input type="text" name="business" id="businessInput" placeholder="e.g., Retail Store Owner">
                        </div>
                    </div>

                    <!-- civil status -->
                    <div class="form-group col-4">
                        <span style="font-size: 13px; color: #555; margin-bottom: 2px;">Civil Status</span>
                        <div class="radio-row">
                            <label class="radio-item"><input type="radio" name="civil-radio" value="single"> Single</label>
                            <label class="radio-item"><input type="radio" name="civil-radio" value="married"> Married</label>
                            <label class="radio-item"><input type="radio" name="civil-radio" value="widow"> Widow</label>
                        </div>
                    </div>
                    <div class="form-group col-4">
                        <input type="text" placeholder="Religion">
                    </div>

                </div>
            </div>

            <!-- FamilyInfo -->
             <h3 class="section-title" style="margin-top: 35px;">Family Information</h3>
            <div class="white-card">

                <!-- Mother's Maiden Name -->
                <div class="row-section">
                    <div class="row-title" style="font-size: 15px;">
                        Mother's Maiden Name
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group col-4">
                        <input type="text" placeholder="First Name">
                    </div>
                    <div class="form-group col-4">
                        <input type="text" placeholder="Last Name">
                    </div>
                    <div class="form-group col-4">
                        <input type="text" placeholder="Middle Name">
                    </div>
                </div>
                
                <!-- Father's Name -->
                 <div class="row-section" style="margin-top: 20px">
                    <div class="row-title" style="font-size: 15px">
                        Father's Name
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group col-4">
                        <input type="text" placeholder="First Name">
                    </div>
                    <div class="form-group col-4">
                        <input type="text" placeholder="Last Name">
                    </div>
                    <div class="form-group col-4">
                        <input type="text" placeholder="Middle Name">
                    </div>
                </div>

                <!-- Spouse Name -->
                 <div class="row-section" style="margin-top: 20px">
                    <div class="row-title" style="font-size: 15px">
                        Spouse's Name
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group col-4">
                        <input type="text" placeholder="First Name">
                    </div>
                    <div class="form-group col-4">
                        <input type="text" placeholder="Last Name">
                    </div>
                    <div class="form-group col-4">
                        <input type="text" placeholder="Middle Name">
                    </div>
                </div>
            </div>


            <div class="white-card">
                <div class="form-grid">
                    <div class="form-group col-6">
                        <div class="row-section" style="flex-direction: column;">
                            <div class="row-title" style="margin-bottom:10px; margin-left:15px; font-size:15px; font-weight: 600;">PhilHealth Information
                            </div>
                            <div class="fieldset-group col-5" style="display: flex; flex-direction:column; gap:8px; width: 100%;">

                                <div class="checkbox-row" style=" justify-content:space-evenly;">
                                    <legend>PhilHealth Status</legend>
                                    <label class="checkbox-item"><input type="radio" name="philStatusInput" onclick="handlePhilHChange()" value="member"> Member</label>
                                    <label class="checkbox-item"><input type="radio" name="philStatusInput"  onclick="handlePhilHChange()" value="dependent"> Dependent</label>
                                    <label class="checkbox-item"><input type="radio" name="philStatusInput" onclick="handlePhilHChange()" value="not applicable">Not Applicable</label>
                                </div>
                                <div class="fieldset-group col-5 num-item" id="philNumDiv" style="border:none; display: none;">
                                    <div>
                                        <legend>PhilHealth Number</legend>
                                        <input type="text" id="philHNumInput" class="inline-input-line" placeholder="e.g., 09-050021346-8" style="border: none; border-bottom: 1px solid #777; flex: 1;">
                                    </div>
                                </div>
                                <div class="fieldset-group col-5 princ-item" id="prinMemDiv" style="border:none; display: none;">
                                    <div>
                                        <legend>Principal Member</legend>
                                        <input type="text"  id="prinMemInput" class="inline-input-line" placeholder="Full Name" style="border: none; border-bottom: 1px solid #777; flex: 1;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-6">
                        <div class="row-section" style="flex-direction: column;">
                            <div class="row-title" style="margin-bottom:10px; margin-left:15px; font-size:15px; font-weight: 600;">
                                National Household Targeting System Information
                            </div>
                            <div class="fieldset-group col-3" style="display: flex; flex-direction:column; gap: 8px; width: 100%;">
                                <div class="checkbox-row" style="padding-left:6px;">
                                    <legend>NHTS Member</legend>
                                    <label class="checkbox-item"><input type="radio" name="nhtsInput"  onclick="handleNhtsChange()" value="yes">Yes</label>
                                    <label class="checkbox-item"><input type="radio" name="nhtsInput"  onclick="handleNhtsChange()" value="no">No</label>
                                </div>
                                <div class="fieldset-group col-5 nhts-item" id="nhtsNumInputDiv" style="border:none; display:none;">
                                    <Legend>NHTS Number</Legend>
                                    <input type="text" class="inline-input-line"
                                    id="nhtsNumInput" placeholder="e.g., 0401-02012-0001" style="border: none; border-bottom: 1px solid #777; flex: 1;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-grid">
                <div class="button-group col-3 button">
                    <button type="button" class="clear-btn">Clear</button> 
                    <button type="submit" class="submit-btn">Register</button>
                </div>
            </div>
        </form>
    </div>
</body>
<script>
</script>
</html>