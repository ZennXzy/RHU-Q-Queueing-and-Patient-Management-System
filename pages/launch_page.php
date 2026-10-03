<?php
    require '../api/check-private.php';
    require '../php/connection.php';

    $userRole = $_SESSION['logged_role'] ?? '';
    
    if ($userRole === 'Display') {
        $targetPage = '../pages/queue_display_page.php';
    } else if ($userRole === 'Doctor/Staff') {
        $targetPage = '../pages/queue_manager_page.php';
    } else if ($userRole === 'Patient') {
        $targetPage = '../pages/queue_display_page.php';
    } else {
        $targetPage = '../pages/register_page.php'; // Default for Admin / Staff
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: Queuing and Patient Management System</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/launch.css">
    <link rel="stylesheet" href="../styles/toast.css">

    <script src="../js/toast.js" defer></script>
    <script src="../js/session-check.js" defer></script>
</head>
<div class="toast-container" id="toastContainer"></div>
<body>
    <div id="loading-screen">
        <div class="rhu-logo">
            <img src="../images/rhu_logo_with_name.png" alt="rhu_logo">
        </div>
        <div class="loading-text" style="text-align: center;">
        <p>RHU-Q: Queueing & Patient Management System</p>
        </div>
        <div class="spinner"></div>
    </div>
</body>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        const destination = <?php echo json_encode($targetPage); ?>;

        const assetsToLoad = [
            '../images/rhu_logo_with_name.png',
            '../images/rhu_logo_topNav.png'
        ];

        const MINIMUM_SPLASH_TIME = 3500;
        const startTime = Date.now();

        const preloadImage = (src) => {
            return new Promise((resolve) => {
                const img = new Image();
                img.src = src;
                img.onload = resolve;
                img.onerror = resolve; 
            });
        };

        const assetPromises = assetsToLoad.map(preloadImage);

        Promise.all(assetPromises).then(() => {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, MINIMUM_SPLASH_TIME - elapsedTime);

            setTimeout(() => {
                document.body.classList.add('page-exit');

                setTimeout(() => {
                    window.location.href = destination;
                }, 700); 
            }, remainingTime);
        });
    });
</script>
</html>