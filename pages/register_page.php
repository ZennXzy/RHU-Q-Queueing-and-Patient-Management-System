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
            <h2 class="page-title">Patient Registration</h2>
            <h3 class="section-title">Personal Information</h3>
            <div class="white-card">
                <div class="form-grid">    
                    <div class="form-group col-4">
                        <input type="text" id="lname" placeholder="Last Name">
                    </div>
                    <div class="form-group col-3">
                        <input type="text" id="fname" placeholder="First Name">
                    </div>
                    <div class="form-group col-3">
                        <input type="text" id="mname" placeholder="Middle Name">
                    </div>
                    <div class="form-group col-2">
                        <input type="text" id="sname" placeholder="Suffixes ">
                    </div>

                    <div class="form-group col-6">
                        <input type="text" id="address" placeholder="Complete Address">
                    </div>
                    <div class="form-group col-1">
                        <input type="text" id="age" placeholder="Age">
                    </div>
                    <div class="form-group col-2">
                        <input type="text" id="religion" placeholder="Religion">
                    </div>
                    <div class="form-group col-2" style="padding-left: 10px; padding-right: 10px;">
                        <span style="font-size: 13px; color: #555;">Sex</span>
                        <div class="checkbox-row" style="justify-content:space-evenly;">
                            <label class="checkbox-item"><input type="checkbox" class="gender-checkbox" value="male"> Male</label>
                            <label class="checkbox-item"><input type="checkbox" class="gender-checkbox" value="female"> Female</label>
                            <label class="checkbox-item"><input type="checkbox" class="gender-checkbox" value="others"> Others</label>
                        </div>
                    </div>

                    <div class="form-group col-4">
                        <span style="font-size: 13px; color: #555;">Date of Birth</span>
                        <input type="text" id="birthDate" placeholder="dd/mm/yyyy" style="margin-top:10px;">
                    </div>
                    <div class="form-group col-4">
                        <span style="font-size: 13px; color: #555;">Contact Number</span>
                        <input type="text" id="contactnum" placeholder="09-123-456-7890" style="margin-top:10px;">
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
                            <label class="radio-item"><input type="radio" name="civilStatus" value="single" onclick="handleCivilChange()">Single</label>
                            <label class="radio-item"><input type="radio" name="civilStatus" value="married" onclick="handleCivilChange()">Married</label>
                            <label class="radio-item"><input type="radio" name="civilStatus" value="widow" onclick="handleCivilChange()">Widow</label>
                        </div>
                    </div>
                    <div class="form-group col-4">
                        <span style="font-size: 13px; color: #555;">Email</span>
                        <input type="text" id="emailContact" placeholder="e.g., johndoe21@gmail.com" style="margin-top:10px;">
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
                        <input type="text" id="mlname" placeholder="Last Name">
                    </div>
                    <div class="form-group col-4">
                        <input type="text" id="mfname" placeholder="First Name">
                    </div>
                    <div class="form-group col-4">
                        <input type="text" id="mmname" placeholder="Middle Name">
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
                        <input type="text" id="flname" placeholder="Last Name">
                    </div>
                    <div class="form-group col-4">
                        <input type="text" id="ffname" placeholder="First Name">
                    </div>
                    <div class="form-group col-4">
                        <input type="text" id="fmname" placeholder="Middle Name">
                    </div>
                </div>

                <!-- Spouse Name -->
                 <div id="spouseDiv" style="display:none;">
                    <div class="row-section" style="margin-top: 20px">
                        <div class="row-title" style="font-size: 15px">
                            Spouse's Name
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group col-4">
                            <input type="text" id="slname" placeholder="Last Name">
                        </div>
                        <div class="form-group col-4">
                            <input type="text" id="sfname" placeholder="First Name">
                        </div>
                        <div class="form-group col-4">
                            <input type="text" id="smname" placeholder="Middle Name">
                        </div>
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
                    <input type="reset" class="clear-btn" value="reset">
                    <button type="submit" class="submit-btn">Register</button>
                </div>
            </div>
        </form>
    </div>
</body>
<!-- selectors script -->
<script>
    // employment-selector
    function handleEmploymentChange() {
        const occupationDiv = document.getElementById('occupationDiv');
        const businessDiv = document.getElementById('businessDiv');
        const occupationInput = document.getElementById('occupationInput');
        const businessInput = document.getElementById('businessInput');

        const employmentRadio = document.querySelector('input[name="employment_status"]:checked');

        if (!employmentRadio) return;

        if (employmentRadio.value === 'employed') {
            occupationDiv.style.display = 'block';
            businessDiv.style.display = 'none';
            businessInput.value = ''; // Clear business input
        } else if (employmentRadio.value === 'self-employed') {
            occupationDiv.style.display = 'none';
            businessDiv.style.display = 'block';
            occupationInput.value = ''; // Clear occupation input
        } else { // unemployed
            occupationDiv.style.display = 'none';
            businessDiv.style.display = 'none';
            occupationInput.value = '';
            businessInput.value = '';
        }
    }

    // gender selector
    const gender_checkboxes = document.querySelectorAll(".gender-checkbox");
    gender_checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", function() {
            if (this.checked) {
                gender_checkboxes.forEach(other => {
                    if (other !== this) {
                        other.checked = false;
                    }
                });
            }
        });
    });


    // philhealth selector
    function handlePhilHChange() {
        const philNumDiv = document.getElementById('philNumDiv');
        const prinMemberDiv = document.getElementById('prinMemDiv');
        const philHNumInput = document.getElementById('philHNumInput');
        const prinMemInput = document.getElementById('prinMemInput');

        const philRadio = document.querySelector('input[name="philStatusInput"]:checked');

        if(!philRadio) return;

        if (philRadio.value === 'member') {
            philNumDiv.style.display = 'block';
            prinMemberDiv.style.display = 'none';
            prinMemberDiv.value = ''; // Clear business input
        } else if (philRadio.value === 'dependent') {
            philNumDiv.style.display = 'block';
            prinMemberDiv.style.display = 'block';
        } else { // not applicable
            philNumDiv.style.display = 'none';
            prinMemberDiv.style.display = 'none';
            philHNumInput.value = ''; 
            prinMemInput.value = ''; 
        }
    }

    // nhts selector
    function handleNhtsChange() {
        const nhtsNumInputDiv = document.getElementById('nhtsNumInputDiv');
        const nhtsNumInput = document.getElementById('nhtsNumInput');

        const nhtsRadio = document.querySelector('input[name="nhtsInput"]:checked');

        if(!nhtsRadio) return;

        if (nhtsRadio.value === 'yes') {
            nhtsNumInputDiv.style.display = 'block';
        
        } else { // no
            nhtsNumInputDiv.style.display = 'none';
            nhtsNumInput.value = '';
        }
    }


    // civil status selector
    function handleCivilChange() {
        const spouseDiv = document.getElementById('spouseDiv');
        const slname = document.getElementById('slname');
        const sfname = document.getElementById('sfname');
        const smname = document.getElementById('smname');

        const civilRadio = document.querySelector('input[name="civilStatus"]:checked');

        if(!civilRadio) return;

        if (civilRadio.value === 'married') {
            spouseDiv.style.display = 'block';
            console.log("Married is selected");
        } else if (civilRadio.value === 'widow') {
            spouseDiv.style.display = 'block';
            console.log("Widow is selected");

        } else { // single
            spouseDiv.style.display = 'none';
            slname.value = '';
            sfname.value = '';
            smname.value = '';
            console.log("Single is selected");
        }
    }


</script>
</html>