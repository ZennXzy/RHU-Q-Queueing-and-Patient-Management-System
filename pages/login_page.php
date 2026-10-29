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
    <link rel="stylesheet" href="../styles/toast.css">

    <script src="../js/login-form.js" defer></script>
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
                    <a href="../pages/patient_reg_form.php" id="acceptTermsBtn" class="btn-accept">Accept &amp; Proceed</a>
                </div>
            </div>
        </main>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loginCard = document.getElementById('loginCard');
        const termsCard = document.getElementById('termsCard');
        
        const openTermsBtn = document.getElementById('openTermsBtn');
        const cancelTermsBtn = document.getElementById('cancelTermsBtn');

        // Show Terms Card, Hide Login Card
        if (openTermsBtn) {
            openTermsBtn.addEventListener('click', function () {
                loginCard.style.display = 'none';
                termsCard.style.display = 'flex';
            });
        }

        // Hide Terms Card, Show Login Card
        if (cancelTermsBtn) {
            cancelTermsBtn.addEventListener('click', function () {
                termsCard.style.display = 'none';
                loginCard.style.display = 'block';
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