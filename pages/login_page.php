<?php
    require_once '../api/check-public.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: Login</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    
    <link rel="stylesheet" href="../styles/login_page.css">
    <link rel="stylesheet" href="../styles/toast.css">

    <script src="../js/login-form.js" defer></script>
    <script src="../js/toast.js" defer></script>
</head>
<body>

    <div class="background-container">
        <div class="toast-container" id="toastContainer"></div>

        <div class="header-text" id="header-text">
            <h1>Welcome to RHU-Q</h1>
            <p>Queuing & Patient Management System</p>
        </div>

        <div class="auth-container">
            <!-- Login Card -->
            <div class="login-card" id="loginCard">
                <div class="rhu-logo">
                    <img src="../images/rhu_logo_with_name.png" alt="rhu_logo">
                </div>
                <div class="login-title">Login</div>
                
                <form action="../php/login.php" method="post" id="loginForm">
                    <div class="input-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" autocomplete="username" require_onced>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" autocomplete="current-password" require_onced>
                    </div>
                    
                    <button type="submit" id="loginBtn" class="login-btn" name="login">Login</button>
                </form>

                <div class="register-divider">
                    <span>Don't have an account?</span>
                </div>

                <button type="button" id="openTermsBtn" class="register-btn">Create Account</button>
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

                <div class="terms-actions">
                    <button type="button" id="cancelTermsBtn" class="btn-cancel">Cancel</button>
                    <a href="../pages/patient_reg_form.php" id="acceptTermsBtn" class="btn-accept">Accept & Proceed</a>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loginCard = document.getElementById('loginCard');
        const termsCard = document.getElementById('termsCard');
        const headerText = document.getElementById('header-text');
        
        const openTermsBtn = document.getElementById('openTermsBtn');
        const cancelTermsBtn = document.getElementById('cancelTermsBtn');

        // Show Terms Card, Hide Login Card
        if (openTermsBtn) {
            openTermsBtn.addEventListener('click', function () {
                loginCard.style.display = 'none';
                termsCard.style.display = 'flex';
                headerText.style.display = 'none';
            });
        }

        // Hide Terms Card, Show Login Card
        if (cancelTermsBtn) {
            cancelTermsBtn.addEventListener('click', function () {
                termsCard.style.display = 'none';
                loginCard.style.display = 'block';
                headerText.style.display = 'block';
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