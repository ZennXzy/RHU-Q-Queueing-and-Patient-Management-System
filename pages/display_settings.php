<?php
require '../php/check-private.php';
authorizeRoles(['Doctor/Staff']);
require '../php/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: Display Settings</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/display-settings.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/navbar.css">


    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/display-settings.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/session-check.js" defer></script>


</head>

<body>
    <?php
        include '../pages/top-navbar.php';
    ?>

    <div class="workspace">
        <?php
            include '../pages/sidebar.php';
        ?>

        <!-- main container -->
        <div class="content-area">
            <div class="content-header">
                <h1 class="page-title">Edit Display Cards</h1>
            </div>
            <div class="cards-container"></div>
        </div>
    </div>
</body>

<dialog id="addCardModal" class="addCardModal">
  <form id="addCardForm" method="dialog">
    <h3 style="margin-bottom: 12px; color: #0B695D;">Add Department Card</h3>
    <label for="deptSelect">Select Department:</label>
    <select id="deptSelect" class="deptSelect" >
      <option value="Medical Consultation">Medical Consultation</option>
      <option value="Prenatal Checkup">Prenatal Checkup</option>
      <option value="Immunization / Vaccination">Immunization / Vaccination</option>
      <option value="Dental Checkup">Dental Checkup</option>
      <option value="Laboratory Request">Laboratory Request</option>

    </select>
    <div class="actionButton">
      <button type="button" id="closeModalBtn" class="closeModalBtn">Cancel</button>
      <button type="submit" id="saveCardBtn" class="saveCardBtn">Add</button>
    </div>
  </form>
</dialog>

</html>