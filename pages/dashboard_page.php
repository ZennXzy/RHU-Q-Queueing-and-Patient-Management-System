<?php
require '../api/check-private.php';
authorizeRoles(['Admin']);
require '../php/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RHU-Q: Dashboard & Analytics</title>
  <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="../styles/sidebar.css">
  <link rel="stylesheet" href="../styles/navbar.css">
  <link rel="stylesheet" href="../styles/dashboard.css">

  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>

  <!-- Script Modules -->
  <script src="../js/submenu.js" defer></script>
  <script src="../js/dot-indicator.js" defer></script>
  <script src="../js/logout.js" defer></script>
  <script src="../js/dashboard.js" defer></script>
</head>
<body>
    <?php include '../pages/top-navbar.php'; ?>

    <div class="workspace">
        <?php include '../pages/sidebar.php'; ?>

        <div class="content-area">
            <div class="dashboard-header-bar">
                <h1 class="page-title">Dashboard & Analytics</h1>
                <!-- Date Range Filter Form -->
                <form id="dashboardFilterForm" class="dashboard-filter">
                    <input type="date" id="dashDateFrom" name="date_from" class="form-control" value="<?= date('Y-m-01') ?>">
                    <span>to</span>
                    <input type="date" id="dashDateTo" name="date_to" class="form-control" value="<?= date('Y-m-d') ?>">
                    <button type="submit" class="btn-submit" style="padding: 8px 14px;">Filter</button>
                </form>
            </div>

            <!-- Summary Metric Tiles -->
            <div class="dashboard-tiles">
                <div class="tile tile-blue">
                    <div class="tile-info">
                        <span class="tile-label">Total Patients</span>
                        <h2 class="tile-value" id="valTotalPatients">0</h2>
                    </div>
                    <div class="tile-icon"></div>
                </div>

                <div class="tile tile-orange">
                    <div class="tile-info">
                        <span class="tile-label">Waiting (Today)</span>
                        <h2 class="tile-value" id="valWaiting">0</h2>
                    </div>
                    <div class="tile-icon"></div>
                </div>

                <div class="tile tile-lightblue">
                    <div class="tile-info">
                        <span class="tile-label">In Progress (Today)</span>
                        <h2 class="tile-value" id="valInProgress">0</h2>
                    </div>
                    <div class="tile-icon"></div>
                </div>

                <div class="tile tile-green">
                    <div class="tile-info">
                        <span class="tile-label">Completed (Today)</span>
                        <h2 class="tile-value" id="valCompleted">0</h2>
                    </div>
                    <div class="tile-icon">
                        <img src="../svg/completed.svg" alt="completed-icon" style="filter: brightness(0) saturate(100%)
                                        invert(38%) sepia(69%) saturate(572%)
                                        hue-rotate(75deg) brightness(91%) contrast(88%);">
                    </div>
                </div>
            </div>

            <!-- Graphs Grid -->
            <div class="charts-grid">
                <div class="content-card">
                    <h3>Registration Trend</h3>
                    <div class="chart-container"><canvas id="registrationTrendChart"></canvas></div>
                </div>
                <div class="content-card">
                    <h3>Queue Trend</h3>
                    <div class="chart-container"><canvas id="queueTrendChart"></canvas></div>
                </div>
                <div class="content-card">
                    <h3>Age Distribution</h3>
                    <div class="chart-container"><canvas id="ageDistributionChart"></canvas></div>
                </div>
                <div class="content-card">
                    <h3>Civil Status</h3>
                    <div class="chart-container"><canvas id="civilStatusChart"></canvas></div>
                </div>
                <div class="content-card">
                    <h3>Gender Distribution</h3>
                    <div class="chart-container"><canvas id="genderDistributionChart"></canvas></div>
                </div>
                <div class="content-card">
                    <h3>Service Distribution</h3>
                    <div class="chart-container"><canvas id="serviceDistributionChart"></canvas></div>
                </div>
                <div class="content-card" style="grid-column: span 2;">
                    <h3>Employment Status</h3>
                    <div class="chart-container" style="max-height: 320px;"><canvas id="employmentStatusChart"></canvas></div>
                </div>
            </div>

            <!-- Text-Type Data Summary Section -->
            <div class="content-card text-summary-card">
                <h3>📊 Analytics Data Summary</h3>
                <div id="textDataSummary" class="text-summary-content">
                    <p>Loading analytics data summary...</p>
                </div>
            </div>

        </div>
    </div>
</body>
</html>