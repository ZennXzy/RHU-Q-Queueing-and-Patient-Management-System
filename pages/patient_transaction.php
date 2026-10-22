<?php
require_once '../api/check-private.php';
authorizeRoles(['Patient']);
require_once '../php/connection.php';
$currentPage = 'transactions';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: My Transactions</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/patient_sidebar.css">
    <link rel="stylesheet" href="../styles/patient_transactions.css">
    <link rel="stylesheet" href="../styles/logout_modal.css">
    <link rel="stylesheet" href="../styles/toast.css">

    <script src="../js/patient_sidebar.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/session-check.js" defer></script>
    <script src="../js/patient_transactions.js" defer></script>
  </head>
  <body>

    <div class="workspace">
      <!-- Main Content Area -->
      <div class="content-area">
        <?php include '../pages/patient_queue_aside.php'; ?>
        <header class="top-bar">
          <div class="toast-container" id="toastContainer"></div>
          <?php include '../pages/patient_sidebar.php'; ?>
          <h1 class="page-title">Transaction History</h1>
        </header>

        <div class="transactions-page-container">
          <!-- Search & Filter Controls -->
          <div class="filter-card">
            <div class="search-box">
              <input type="text" id="transactionSearchInput" placeholder="Search by queue code or service name...">
            </div>
          </div>

          <!-- Transactions Container -->
          <div class="transactions-card">
            <div class="card-header">
              <h2>Recent Visits & Services</h2>
              <span id="transactionCountBadge" class="count-badge">0 Records</span>
            </div>

            <div id="transactionsList" class="transactions-list">
              <div class="loading-spinner">Loading your transactions...</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>