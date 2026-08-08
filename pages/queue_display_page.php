<?php
  require '../php/check-private.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: Queue Display</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/queue_display.css">

    <script src="../js/queue-display.js" defer></script>
</head>
<body>
    <div class="page-container">
        <!-- Top branding title -->
        <div class="top-brand"><div class="rhu_logo_topNav"><img src="../images/rhu_logo_topNav.png" alt="rhu_logo"></div> RHU-Q: Queuing and Patient Management System</div>

        <!-- Heading -->
        <h1 class="page-title">Queue Display</h1>

        <!-- White Main Card Container -->
        <div class="display-card">
            
            
            <!-- Fullscreen Icon -->
            <div class="minimize-btn" title="Toggle Fullscreen">
                <a href="../pages/queue_manager_page.php" class="minimize-button" title="Exit Display"><img src="../svg/minimize-icon.svg" alt="minimize-button"></a>
            </div>
        </div>
    </div>
</body>
</html>