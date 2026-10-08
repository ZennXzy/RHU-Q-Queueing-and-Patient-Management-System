<?php
require_once '../api/check-public.php';
require_once '../php/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: Account Registration</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/patient_reg_form.css">
    <link rel="stylesheet" href="../styles/toast.css">

    <script src="../js/toast.js" defer></script>
    <script src="../js/account-registration.js" defer></script>
</head>
<body>
  <div class="background-container">
    <div class="workspace centered-workspace">
        <div class="toast-container" id="toastContainer"></div>
        
        <!-- Main Form Container -->
        <form id="accountRegistrationForm" class="content-area centered-content">
            
            <!-- Top Navigation / Header Bar -->
             <div class="rhu-logo">
                <img src="../images/rhu_logo_with_name.png" alt="rhu_logo">
            </div>
            <div class="form-header">
                <a href="javascript:history.back()" class="back-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back
                </a>
                <h2 class="page-title" id="pageTitle">Account Registration</h2>
            </div>

            <!-- STEP 1: PATIENT INFORMATION CARDS -->
            <div id="patientInfoStep">
                <!-- Personal Information -->
                <div class="card-wrapper">
                    <h3 class="section-title">Personal Information</h3>
                    <div class="white-card">
                        <div class="form-grid">    
                            <div class="form-group col-4">
                                <input type="text" name="lname" id="lname" placeholder="Last Name" autocomplete="off" require_onced>
                            </div>
                            <div class="form-group col-3">
                                <input type="text" name="fname" id="fname" placeholder="First Name" autocomplete="off" require_onced>
                            </div>
                            <div class="form-group col-3">
                                <input type="text" name="mname" id="mname" placeholder="Middle Name" autocomplete="off">
                            </div>
                            <div class="form-group col-2 input-select">
                                <select name="sname" id="sname">
                                    <option value="" disabled selected>Suffix</option>
                                    <option value="Jr.">Jr. (Junior)</option>
                                    <option value="Sr.">Sr. (Senior)</option>
                                    <option value="I">I</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                </select>
                            </div>

                            <div class="form-group col-4">
                                <input type="text" name="address" id="address" placeholder="Complete Address" require_onced>
                            </div>
                            <div class="form-group col-3">
                                <div class="date">
                                    <span style="font-size: 13px; color: #555;">Date of Birth:</span>
                                    <input type="date" id="date_of_birth" name="birthDate">
                                </div>
                            </div>
                            <div class="form-group col-2">
                                <input type="text" id="age_display" name="age" readonly placeholder="Age" title="Autofill Age">
                            </div>
                            <div class="form-group col-3 input-select">
                                <select name="religion" id="religion">
                                    <option value="" disabled selected>Religion</option>
                                    <option value="Roman Catholicism">Roman Catholic</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Protestant and Evangelical Groups">Protestant and Evangelical Groups</option>
                                    <option value="Iglesia ni Cristo">Iglesia ni Cristo (INC)</option>
                                    <option value="Philippine Independent Church (Aglipayan)">Philippine Independent Church (Aglipayan)</option>
                                    <option value="Other Groups">Other Groups</option>
                                </select>
                            </div>
                            <div class="form-group col-4"> 
                                <span style="font-size: 13px; color: #555;">Sex</span>
                                <div class="input-container" style="margin-top:10px; ">
                                    <div class="checkbox-row" style="justify-content:space-evenly;">
                                        <label class="checkbox-item"><input type="checkbox" name="gender" class="gender-checkbox" value="Male"> Male</label>
                                        <label class="checkbox-item"><input type="checkbox" name="gender" class="gender-checkbox" value="Female"> Female</label>
                                        <label class="checkbox-item"><input type="checkbox" name="gender" class="gender-checkbox" value="Others"> Others</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-3">
                                <span style="font-size: 13px; color: #555;">Contact Number</span>
                                <input type="text" name="contactnum" id="contactnum" placeholder="09123456789" style="margin-top:10px;" autocomplete="off">
                            </div>
                            <div class="form-group col-5" style="grid-row: span 2; justify-content: flex-start;">
                                <span style="font-size: 13px; color: #555;">Employment Status</span>

                                <div class="radio-row input-container" style="justify-content:space-evenly;">
                                    <label class="radiobox-item">
                                        <input type="radio" name="employment_status" class="employment-status employed" value="Employed" onclick="handleEmploymentChange()"> Employed 
                                    </label>
                                    <label class="radiobox-item">
                                        <input type="radio" name="employment_status" class="employment-status self-employed" value="Self-Employed" onclick="handleEmploymentChange()"> Self-Employed
                                    </label>
                                    <label class="radiobox-item">
                                        <input type="radio" name="employment_status" class="employment-status unemployed" value="Unemployed" onclick="handleEmploymentChange()"> Unemployed
                                    </label>
                                </div>

                                <div class="fieldset-group col-5 occupation-item" id="occupationDiv" style="margin-top:-1px; display: none;">
                                    <legend>Occupation</legend>
                                    <input type="text" name="occupation" id="occupationInput" placeholder="e.g., Software Engineer">
                                </div>

                                <div class="fieldset-group col-5 business-item" id="businessDiv" style="margin-top:-1px; display: none;">
                                    <legend>Business <span>Type n/a if not applicable</span></legend>
                                    <input type="text" name="business" id="businessInput" placeholder="e.g., Retail Store Owner">
                                </div>
                            </div>

                            <div class="form-group col-4">
                                <span style="font-size: 13px; color: #555;">Civil Status</span>
                                <div class="radio-row input-container" style="justify-content:space-evenly;">
                                    <label class="radio-item"><input type="radio" name="civilStatus" value="Single" onclick="handleCivilChange()">Single</label>
                                    <label class="radio-item"><input type="radio" name="civilStatus" value="Married" onclick="handleCivilChange()">Married</label>
                                    <label class="radio-item"><input type="radio" name="civilStatus" value="Widow" onclick="handleCivilChange()">Widow</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Family Info -->
                <div class="card-wrapper">
                    <h3 class="section-title">Family Information</h3>
                    <div class="white-card">
                        <div class="row-section"><div class="row-title" style="font-size: 15px;">Mother's Maiden Name</div></div>
                        <div class="form-grid">
                            <div class="form-group col-4"><input type="text" name="mlname" id="mlname" placeholder="Last Name" autocomplete="off"></div>
                            <div class="form-group col-4"><input type="text" name="mfname" id="mfname" placeholder="First Name" autocomplete="off"></div>
                            <div class="form-group col-4"><input type="text" name="mmname" id="mmname" placeholder="Middle Name" autocomplete="off"></div>
                        </div>
                        
                        <div class="row-section" style="margin-top: 20px"><div class="row-title" style="font-size: 15px">Father's Name</div></div>
                        <div class="form-grid">
                            <div class="form-group col-4"><input type="text" name="flname" id="flname" placeholder="Last Name" autocomplete="off"></div>
                            <div class="form-group col-3"><input type="text" name="ffname" id="ffname" placeholder="First Name" autocomplete="off"></div>
                            <div class="form-group col-3"><input type="text" name="fmname" id="fmname" placeholder="Middle Name" autocomplete="off"></div>
                            <div class="form-group col-2 input-select">
                                <select name="fsname" id="fsname">
                                    <option value="" disabled selected>Suffix</option>
                                    <option value="Jr.">Jr. (Junior)</option>
                                    <option value="Sr.">Sr. (Senior)</option>
                                    <option value="I">I</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                </select>
                            </div>
                        </div>

                        <div id="spouseDiv" style="display:none;">
                            <div class="row-section" style="margin-top: 20px"><div class="row-title" style="font-size: 15px">Spouse's Name</div></div>
                            <div class="form-grid">
                                <div class="form-group col-4"><input type="text" name="slname" id="slname" placeholder="Last Name" autocomplete="off"></div>
                                <div class="form-group col-3"><input type="text" name="sfname" id="sfname" placeholder="First Name" autocomplete="off"></div>
                                <div class="form-group col-3"><input type="text" name="smname" id="smname" placeholder="Middle Name" autocomplete="off"></div>
                                <div class="form-group col-2 input-select">
                                    <select name="ssname" id="ssname">
                                        <option value="" disabled selected>Suffix</option>
                                        <option value="Jr.">Jr. (Junior)</option>
                                        <option value="Sr.">Sr. (Senior)</option>
                                        <option value="I">I</option>
                                        <option value="II">II</option>
                                        <option value="III">III</option>
                                        <option value="IV">IV</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PhilHealth Info -->
                <div class="card-wrapper">
                    <div class="white-card">
                        <div class="form-grid">
                            <div class="form-group col-6">
                                <div class="row-section" style="flex-direction: column;">
                                    <div class="row-title" style="margin-bottom:10px; margin-left:15px; font-size:15px; font-weight: 600;">PhilHealth Information</div>
                                    <div class="fieldset-group col-5" style="display: flex; flex-direction:column; gap:8px; width: 100%;">
                                        <div class="checkbox-row" style="justify-content:space-evenly;">
                                            <legend>PhilHealth Status</legend>
                                            <label class="checkbox-item"><input type="radio" name="philStatusInput" onclick="handlePhilHChange()" value="Member"> Member</label>
                                            <label class="checkbox-item"><input type="radio" name="philStatusInput" onclick="handlePhilHChange()" value="Dependent"> Dependent</label>
                                            <label class="checkbox-item"><input type="radio" name="philStatusInput" onclick="handlePhilHChange()" value="Not Applicable">Not Applicable</label>
                                        </div>
                                        <div class="fieldset-group col-5 num-item" id="philNumDiv" style="border:none; display: none;">
                                            <div>
                                                <legend>PhilHealth Number</legend>
                                                <input type="text" id="philHNumInput" name="philNumber" class="inline-input-line" placeholder="e.g., 09-050021346-8" style="border: none; border-bottom: 1px solid #777; flex: 1;" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="fieldset-group col-5 princ-item" id="prinMemDiv" style="border:none; display: none;">
                                            <div>
                                                <legend>Principal Member</legend>
                                                <input type="text" id="prinMemInput" name="prinMember" class="inline-input-line" placeholder="Full Name" style="border: none; border-bottom: 1px solid #777; flex: 1;" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-6">
                                <div class="row-section" style="flex-direction: column;">
                                    <div class="row-title" style="margin-bottom:10px; margin-left:15px; font-size:15px; font-weight: 600;">Yakap PhilHealth</div>
                                    <div class="fieldset-group col-3" style="display: flex; flex-direction:column; gap: 8px; width: 100%;">
                                        <div class="checkbox-row" style="padding-left:6px;">
                                            <legend>Yakap Member</legend>
                                            <label class="checkbox-item"><input type="radio" name="yakapmem" value="Yes">Yes</label>
                                            <label class="checkbox-item"><input type="radio" name="yakapmem" value="No">No</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-wrapper">
                    <div class="button-group-container">
                        <input type="reset" class="clear-btn" value="Reset">
                        <button type="button" id="nextStepBtn" class="submit-btn">Next Step</button>
                    </div>
                </div>
            </div>

            <!-- STEP 2: CREDENTIALS CARD -->
            <div id="credentialsStep" style="display: none;">
                <div class="card-wrapper">
                    <h3 class="section-title">Create Account Credentials</h3>
                    <div class="white-card">
                        <div class="form-grid">
                            <!-- Account Token Field (Read-only + Copy Button) -->
                            <div class="form-group col-12">
                                <label style="font-size: 13px; color: #555;">Account Token (Auto-Generated)</label>
                                <div style="display: flex; gap: 8px; margin-top: 5px;">
                                    <input type="text" name="account_token" id="account_token" readonly style="padding: 11px 14px; border: 1px solid #0B695D; border-radius: 6px; font-weight: bold; background-color: #f4f4f4; letter-spacing: 2px; flex: 1;">
                                    <button type="button" id="copyTokenBtn" class="clear-btn" style="background-color: #0B695D; color: white; width: auto; padding: 0 16px; margin: 0;">Copy</button>
                                </div>
                            </div>

                            <div class="form-group col-12">
                                <label style="font-size: 13px; color: #555;">Username *</label>
                                <input type="text" name="username" id="username" placeholder="Choose a unique username" autocomplete="off" require_onced style="margin-top: 5px; padding: 11px 14px; border: 1px solid #0B695D; border-radius: 6px;">
                            </div>

                            <div class="form-group col-12">
                                <label style="font-size: 13px; color: #555;">Email Address (Optional)</label>
                                <input type="email" name="email" id="email" placeholder="example@domain.com (Optional)" autocomplete="off" style="margin-top: 5px; padding: 11px 14px; border: 1px solid #0B695D; border-radius: 6px;">
                            </div>

                            <div class="form-group col-6">
                                <label style="font-size: 13px; color: #555;">Password *</label>
                                <input type="password" name="password" id="password" placeholder="Minimum 6 characters" require_onced style="margin-top: 5px; padding: 11px 14px; border: 1px solid #0B695D; border-radius: 6px;">
                            </div>

                            <div class="form-group col-6">
                                <label style="font-size: 13px; color: #555;">Confirm Password *</label>
                                <input type="password" id="confirm_password" placeholder="Re-type password" require_onced style="margin-top: 5px; padding: 11px 14px; border: 1px solid #0B695D; border-radius: 6px;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-wrapper">
                    <div class="button-group-container">
                        <button type="button" id="backStepBtn" class="clear-btn" style="background-color: #718096; color: white;">Back</button>
                        <button type="submit" id="finalRegisterBtn" class="submit-btn">Register</button>
                    </div>
                </div>
            </div>

        </form>
    </div>
  </div>  
</body>

<script>
    // Form logic toggle helper scripts
    function handleEmploymentChange() {
        const occupationDiv = document.getElementById('occupationDiv');
        const businessDiv = document.getElementById('businessDiv');
        const occupationInput = document.getElementById('occupationInput');
        const businessInput = document.getElementById('businessInput');
        const employmentRadio = document.querySelector('input[name="employment_status"]:checked');

        if (!employmentRadio) return;
        if (employmentRadio.value === 'Employed') {
            occupationDiv.style.display = 'block';
            businessDiv.style.display = 'none';
            businessInput.value = '';
        } else if (employmentRadio.value === 'Self-Employed') {
            occupationDiv.style.display = 'none';
            businessDiv.style.display = 'block';
            occupationInput.value = '';
        } else {
            occupationDiv.style.display = 'none';
            businessDiv.style.display = 'none';
            occupationInput.value = '';
            businessInput.value = '';
        }
    }

    const gender_checkboxes = document.querySelectorAll(".gender-checkbox");
    gender_checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", function() {
            if (this.checked) {
                gender_checkboxes.forEach(other => { if (other !== this) other.checked = false; });
            }
        });
    });

    document.getElementById('date_of_birth').addEventListener('change', function() {
        const dobValue = this.value;
        if (!dobValue) return;
        const dob = new Date(dobValue);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) { age--; }
        document.getElementById('age_display').value = age >= 0 ? "Age: " + age : "";
    });

    function handlePhilHChange() {
        const philNumDiv = document.getElementById('philNumDiv');
        const prinMemberDiv = document.getElementById('prinMemDiv');
        const philHNumInput = document.getElementById('philHNumInput');
        const prinMemInput = document.getElementById('prinMemInput');
        const philRadio = document.querySelector('input[name="philStatusInput"]:checked');

        if(!philRadio) return;
        if (philRadio.value === 'Member') {
            philNumDiv.style.display = 'block';
            prinMemberDiv.style.display = 'none';
            prinMemInput.value = '';
        } else if (philRadio.value === 'Dependent') {
            philNumDiv.style.display = 'block';
            prinMemberDiv.style.display = 'block';
        } else {
            philNumDiv.style.display = 'none';
            prinMemberDiv.style.display = 'none';
            philHNumInput.value = ''; 
            prinMemInput.value = ''; 
        }
    }

    function handleCivilChange() {
        const spouseDiv = document.getElementById('spouseDiv');
        const slname = document.getElementById('slname');
        const sfname = document.getElementById('sfname');
        const smname = document.getElementById('smname');
        const civilRadio = document.querySelector('input[name="civilStatus"]:checked');

        if(!civilRadio) return;
        if (civilRadio.value === 'Married' || civilRadio.value === 'Widow') {
            spouseDiv.style.display = 'block';
        } else {
            spouseDiv.style.display = 'none';
            slname.value = ''; sfname.value = ''; smname.value = '';
        }
    }
</script>
</html>