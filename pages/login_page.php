<?php
    require '../php/check-public.php';
    require '../php/connection.php';
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
</head>
<body>

    <div class="background-container">
        <div class="toast-container" id="toastContainer"></div>

        <div class="header-text">
            <h1>Welcome to RHU-Q</h1>
            <p>Queuing & Patient Management System</p>
        </div>

        <div class="login-card">
            <div class="rhu-logo">
                <img src="../images/rhu_logo_with_name.png" alt="rhu_logo">
            </div>
            <div class="login-title">Login</div>
            
            <form action="../php/login.php" method="post">
                <div class="input-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username"  autocomplete="off">
                </div>
                
                <div class="input-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" >
                </div>
                
                <button type="submit" id="loginBtn" class="login-btn" name="login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>