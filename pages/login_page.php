<?php
session_start();
// If user is already logged in, redirect them to their respective workspace/dashboard
if (isset($_SESSION['logged_id']) || isset($_SESSION['patient_id'])) {
    $role = strtolower($_SESSION['user_type'] ?? $_SESSION['logged_role'] ?? '');

    if ($role === 'patient') {
        header('Location: ../pages/launch_page.php');
        exit();
    } else {
        header('Location: ../pages/launch_page.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0B695D">
    <title>RHU-Q: Login</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    
    <link rel="stylesheet" href="../styles/login_page.css">
    <link rel="stylesheet" href="../styles/activate_modal.css">
    <link rel="stylesheet" href="../styles/toast.css">

    <script src="../js/login-form.js" defer></script>
    <script src="../js/reset-password.js" defer></script>
    <script src="../js/toast.js" defer></script>
</head>
<body>
    <div class="toast-container" id="toastContainer"></div>

    <div class="background-container">

        <!-- Left side: welcome message -->
        <aside class="hero" id="header-text">
            <div class="hero-brand">
                <img src="../images/rhu_logo_topNav.png" alt="RHU logo">
                <span>RHU-Q</span>
            </div>

            <div class="hero-copy">
                <h1>Welcome to RHU-Q</h1>
                <p class="hero-sub">Queuing &amp; Patient Management System</p>

                <ul class="hero-points">
                    <li>Follow your queue number in real time</li>
                    <li>Book and track your appointments</li>
                    <li>Keep patient records in one place</li>
                </ul>
            </div>
        </aside>

        <!-- Right side: login form / terms -->
        <main class="auth-container">
            <!-- Login Card -->
            <div class="login-card" id="loginCard">
                <h2 class="login-title">Welcome back</h2>
                <p class="login-subtitle">Enter your username and password to continue.</p>
                
                <form action="../php/login.php" method="post" id="loginForm">
                    <div class="input-group">
                        <label for="username">Username</label>
                        <div class="input-field">
                            <input type="text" id="username" name="username" autocomplete="username" autocapitalize="none" spellcheck="false" required>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="input-field input-field--password">
                            <input type="password" id="password" name="password" autocomplete="current-password" required>
                            <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password" aria-pressed="false">
                                <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path>
                                    <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" id="loginBtn" class="login-btn" name="login">Login</button>
                </form>

                <div class="register-divider">
                    <span>Don't have an account?</span>
                </div>

                <button type="button" id="openTermsBtn" class="register-btn">Create Account</button>
                <a href="#" id="openResetModalBtn" style="font-size: 13px; color: #0B695D; text-decoration: none; display: flex; justify-content: center; margin-top: 10px;">Forgot Password?</a>
            </div>

            <!-- Terms of Agreement Card (Hidden by Default) -->
            <div class="terms-card" id="termsCard" style="display: none;">
                <div class="terms-title">Terms of Agreement</div>
                
                <div class="terms-content">
                    <p>Welcome to RHU-Q. By registering for an account, you agree to comply with and be bound by the following terms and conditions:</p>
                    <ul>
                        <li>You confirm that all personal and medical information provided is accurate and truthful.</li>
                        <li>Your data will be processed in accordance with our Privacy Policy and local Data Privacy regulations.</li>
                        <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
                        <li>Unauthorized use of another patient's identity or false appointments is strictly prohibited.</li>
                    </ul>
                </div>
                <div style="margin: 15px 0; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                    <input type="checkbox" id="privacyPolicyCheck" style="width: 16px; height: 16px; cursor: pointer;">
                    <label for="privacyPolicyCheck" style="cursor: pointer; color: #334155;">
                        I agree and read the <a href="#" id="openPrivacyPolicyModal" style="color: #0B695D; text-decoration: underline; font-weight: 600;">Privacy Policy</a>
                    </label>
                </div>

                <div class="terms-actions">
                    <button type="button" id="cancelTermsBtn" class="btn-cancel">Cancel</button>
                    <a href="../pages/patient_reg_form.php" id="acceptTermsBtn" class="btn-accept">Accept &amp; Proceed</a>
                </div>
            </div>
        </main>
    </div>
    <!-- Reset Password Modal -->
    <dialog id="resetModal" class="activate-modal">
        <div class="activate-modal-content">
            <div class="activate-modal-header">
                <h3 class="activate-modal-title">Reset Your Password</h3>
                <button type="button" id="closeResetModalBtn" class="activate-close-btn">&times;</button>
            </div>
            <p class="activate-modal-desc">
                Enter your Account Token, Date of Birth, and Mother's Maiden Name to verify your identity.
            </p>
            <form id="resetPasswordForm">
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div class="activate-form-group">
                        <label>Account Token *</label>
                        <input type="text" name="account_token" placeholder="6-digit token" required autocomplete="off">
                    </div>
                    <div class="activate-form-group">
                        <label>Date of Birth *</label>
                        <input type="date" name="birthdate" required>
                    </div>
                    <div class="activate-form-group">
                        <label>Mother's Maiden Name *</label>
                        <input type="text" name="mothers_name" placeholder="Mother's Full Maiden Name" required autocomplete="off">
                    </div>
                    <div class="activate-form-group">
                        <label>New Password *</label>
                        <input type="password" name="new_password" placeholder="Minimum 6 characters" required>
                    </div>
                    <button type="submit" class="activate-submit-btn">Update Password</button>
                </div>
            </form>
        </div>
    </dialog>
    <!-- Privacy Policy Modal -->
    <dialog id="privacyPolicyModal" class="policy-modal activate-modal">
        <div class="activate-modal-content" style="max-width: 650px; width: 95%;">
            <div class="activate-modal-header">
                <h3 class="activate-modal-title">Data Privacy Policy</h3>
                <button type="button" id="closePrivacyModalBtn" class="activate-close-btn">&times;</button>
            </div>
            
            <div class="activate-modal-desc" style="max-height: 450px; overflow-y: auto; text-align: left; line-height: 1.6; padding-right: 5px; font-size: 13.5px; color: #334155;">
                <p style="font-weight: bold; margin-bottom: 10px;">ODIONGAN RURAL HEALTH UNIT (RHU)</p>
                
                <p>The Rural Health Unit of Odiongan is committed to protecting and respecting your privacy in compliance with Republic Act No. 10173, also known as the Data Privacy Act of 2012 (DPA), and its Implementing Rules and Regulations.</p>
                
                <p style="margin-top: 8px;">This Privacy Policy explains how we collect, use, store, disclose, and dispose of your personal and sensitive personal information when you avail yourself of our healthcare services, visit our facility, or interact with our staff.</p>

                <h4 style="color: #0B695D; margin-top: 15px; margin-bottom: 5px;">1. Information We Collect</h4>
                <p>To provide appropriate medical care, public health interventions, and administrative processing, the RHU collects the following personal and sensitive personal information:</p>
                <ul style="margin-left: 20px; margin-top: 5px;">
                    <li><strong>Personal Information:</strong> Full name, date of birth, age, sex, civil status, home address, contact numbers, email address, and name of next of kin or emergency contact.</li>
                    <li><strong>Sensitive Personal Information:</strong> Medical history, vital signs, physical or mental health conditions, clinical diagnoses, laboratory and diagnostic test results, medications, and PhilHealth or social security numbers.</li>
                    <li><strong>Government-Issued Identification:</strong> Valid IDs presented during registration or admission.</li>
                </ul>

                <h4 style="color: #0B695D; margin-top: 15px; margin-bottom: 5px;">2. Purpose and Methods of Processing</h4>
                <p>The RHU processes your data based on lawful grounds under Sections 12 and 13 of the Data Privacy Act, primarily to fulfill our mandate as a local government healthcare provider. Your information is used for:</p>
                <ul style="margin-left: 20px; margin-top: 5px;">
                    <li><strong>Medical Consultation and Treatment:</strong> Creation, retrieval, and updating of your patient records to deliver medical, dental, maternal, and child health care.</li>
                    <li><strong>Public Health Monitoring:</strong> Reporting communicable diseases and health data to the Department of Health (DOH) or the Provincial Health Office as mandated by law.</li>
                    <li><strong>Special Programs:</strong> Enrollment and management under specialized programs such as TB-DOTS, Animal Bite Treatment, and immunization schedules.</li>
                    <li><strong>PhilHealth and Financial Claims:</strong> Processing medical billing, PhilHealth claims, or local government financial assistance.</li>
                </ul>

                <h4 style="color: #0B695D; margin-top: 15px; margin-bottom: 5px;">3. Data Storage and Retention</h4>
                <ul style="margin-left: 20px; margin-top: 5px;">
                    <li><strong>Storage:</strong> All patient records, queue transactions, and user data are securely stored in a centralized relational database hosted on our secure web server. Access is restricted through encrypted credentials, role-based user authentication, and secure database connections.</li>
                    <li><strong>Retention:</strong> Your digital medical records and transaction history are retained in accordance with Department of Health (DOH) guidelines on medical record retention and applicable local government policies. Once the retention period expires, records are permanently deleted or purged from the database using secure digital sanitization methods.</li>
                </ul>

                <h4 style="color: #0B695D; margin-top: 15px; margin-bottom: 5px;">4. Data Disclosure and Sharing</h4>
                <p>The RHU will not disclose your personal or sensitive health information to third parties without your explicit consent, except in the following circumstances authorized by law:</p>
                <ul style="margin-left: 20px; margin-top: 5px;">
                    <li>When necessary to protect your life, health, or security during an emergency.</li>
                    <li>When mandated by public health reporting laws or submitted electronically to authorized oversight agencies like the Department of Health (DOH) or PhilHealth.</li>
                    <li>Upon valid order of a court or legal authority.</li>
                </ul>

                <h4 style="color: #0B695D; margin-top: 15px; margin-bottom: 5px;">5. Security Measures</h4>
                <p>The RHU implements strict organizational, physical, and technical safeguards within our web application and server environment to protect your data from unauthorized access, alteration, or disclosure:</p>
                <ul style="margin-left: 20px; margin-top: 5px;">
                    <li><strong>Organizational:</strong> Only authorized health personnel (Administrators, Doctors, and Staff) assigned with specific system roles are granted access to patient records and queue dashboards.</li>
                    <li><strong>Physical:</strong> Server hardware and database environments are hosted in secure, access-controlled data centers managed by our hosting provider.</li>
                    <li><strong>Technical:</strong> We utilize secure encrypted connections (HTTPS), password hashing, session validation, and strict role-based access controls to safeguard data transmissions and user accounts.</li>
                </ul>

                <h4 style="color: #0B695D; margin-top: 15px; margin-bottom: 5px;">6. Your Rights as a Data Subject</h4>
                <p>Under the Data Privacy Act of 2012, you are entitled to the following rights regarding your personal information:</p>
                <ul style="margin-left: 20px; margin-top: 5px;">
                    <li><strong>Right to be Informed:</strong> The right to know if your data is being processed and why.</li>
                    <li><strong>Right to Access:</strong> The right to request reasonable access to your medical records or a copy of your personal data.</li>
                    <li><strong>Right to Rectification:</strong> The right to dispute any inaccuracy or error in your data and have it corrected.</li>
                    <li><strong>Right to Object/Erasure:</strong> The right to object to processing or request the suspension/withdrawal of your data, subject to legal and public health retention mandates.</li>
                    <li><strong>Right to File a Complaint:</strong> The right to file a complaint with our Data Protection Officer or the National Privacy Commission (NPC) if your privacy rights have been violated.</li>
                </ul>

                <h4 style="color: #0B695D; margin-top: 15px; margin-bottom: 5px;">7. Contact Information</h4>
                <p>For any questions, clarifications, or requests regarding this Privacy Policy or your data privacy rights, you may contact our Data Protection Officer (DPO):</p>
                <ul style="margin-left: 20px; margin-top: 5px; list-style-type: none; padding-left: 0;">
                    <li><strong>Office:</strong> Rural Health Unit, J.P. Laurel St., Brgy. Tabing Dagat, Odiongan, Romblon</li>
                    <li><strong>Email:</strong> [rhu.email@municipality.gov.ph]</li>
                    <li><strong>Phone Number:</strong> [Insert Phone Number]</li>
                </ul>
            </div>

            <div style="margin-top: 20px; text-align: right;">
                <button type="button" id="closePolicyBtn" class="btn-secondary" style="padding: 8px 18px; background: #0B695D; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Close</button>
            </div>
        </div>
    </dialog>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loginCard = document.getElementById('loginCard');
        const termsCard = document.getElementById('termsCard');
        
        const openTermsBtn = document.getElementById('openTermsBtn');
        const cancelTermsBtn = document.getElementById('cancelTermsBtn');

        // Checkbox and Proceed button elements
        const privacyCheckbox = document.getElementById('privacyPolicyCheck');
        const acceptTermsBtn = document.getElementById('acceptTermsBtn');

        // Privacy Policy Modal Elements
        const privacyPolicyModal = document.getElementById('privacyPolicyModal');
        const openPrivacyPolicyModal = document.getElementById('openPrivacyPolicyModal');
        const closePrivacyModalBtn = document.getElementById('closePrivacyModalBtn');
        const closePolicyBtn = document.getElementById('closePolicyBtn');

        // Show Terms Card, Hide Login Card
        if (openTermsBtn) {
            openTermsBtn.addEventListener('click', function () {
                loginCard.style.display = 'none';
                termsCard.style.display = 'flex';
                // Reset checkbox state when opening card
                if (privacyCheckbox) privacyCheckbox.checked = false;
                updateProceedButtonState();
            });
        }

        // Hide Terms Card, Show Login Card
        if (cancelTermsBtn) {
            cancelTermsBtn.addEventListener('click', function () {
                termsCard.style.display = 'none';
                loginCard.style.display = 'block';
            });
        }

        // Manage button active/disabled state based on checkbox
        function updateProceedButtonState() {
            if (!privacyCheckbox || !acceptTermsBtn) return;
            
            if (privacyCheckbox.checked) {
                acceptTermsBtn.removeAttribute('disabled');
                acceptTermsBtn.style.pointerEvents = 'auto';
            } else {
                acceptTermsBtn.setAttribute('disabled', 'true');
                acceptTermsBtn.style.pointerEvents = 'none';
            }
        }

        if (privacyCheckbox) {
            privacyCheckbox.addEventListener('change', updateProceedButtonState);
        }

        // Open Privacy Policy Modal
        if (openPrivacyPolicyModal) {
            openPrivacyPolicyModal.addEventListener('click', function (e) {
                e.preventDefault();
                if (privacyPolicyModal) privacyPolicyModal.showModal();
            });
        }

        // Close Privacy Policy Modal handlers
        if (closePrivacyModalBtn) {
            closePrivacyModalBtn.addEventListener('click', () => privacyPolicyModal.close());
        }
        if (closePolicyBtn) {
            closePolicyBtn.addEventListener('click', () => privacyPolicyModal.close());
        }
        if (privacyPolicyModal) {
            privacyPolicyModal.addEventListener('click', (e) => {
                const rect = privacyPolicyModal.getBoundingClientRect();
                if (e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom) {
                    privacyPolicyModal.close();
                }
            });
        }

        // Show / hide password
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        if (passwordInput && togglePassword) {
            togglePassword.addEventListener('click', function () {
                const show = passwordInput.type === 'password';
                passwordInput.type = show ? 'text' : 'password';
                togglePassword.classList.toggle('is-visible', show);
                togglePassword.setAttribute('aria-pressed', show ? 'true' : 'false');
                togglePassword.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        }

        // Check if redirected due to expired session
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'expired') {
            if (typeof showToast === 'function') {
                showToast('Your session has expired due to inactivity. Please log in again.', 'warning');
            }
        }
    });
</script>
</body>
</html>