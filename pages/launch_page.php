<?php
    require '../php/check-private.php';
    require '../php/connection.php';
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

    <script src="../js/toast.js"></script>
</head>
<body>

    <div id="loading-screen">
        <div class="toast-container" id="toastContainer"></div>

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
        // Array of key asset paths to preload and verify
        const assetsToLoad = [
        '../images/rhu_logo_with_name.png',
        '../images/rhu_logo_topNav.png'
        ];

        const MINIMUM_SPLASH_TIME = 2500; // Minimum screen display time (2.5 seconds)
        const startTime = Date.now();

        // Promisified image loader
        const preloadImage = (src) => {
        return new Promise((resolve) => {
            const img = new Image();
            img.src = src;
            img.onload = resolve;
            img.onerror = resolve; // Continue even if an asset fails to load
        });
        };

        // Track all image asset loads concurrently
        const assetPromises = assetsToLoad.map(preloadImage);

        // Wait for both asset verification AND minimum splash screen duration
        Promise.all(assetPromises).then(() => {
        const elapsedTime = Date.now() - startTime;
        const remainingTime = Math.max(0, MINIMUM_SPLASH_TIME - elapsedTime);

        setTimeout(() => {
            // Trigger exit animation
            document.body.classList.add('page-exit');

            // Redirect to login page after exit animation completes
            setTimeout(() => {
            window.location.href = "../pages/register_page.php";
            }, 700); // 700ms matches standard CSS fade-out transition duration
        }, remainingTime);
        });
    });
</script>
</html>