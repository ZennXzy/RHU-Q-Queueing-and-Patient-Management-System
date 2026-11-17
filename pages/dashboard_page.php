
<?php
require_once '../api/check-private.php';
authorizeRoles(['Admin', 'Doctor/Staff']);
require_once '../php/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>RHU-Q: Dashboard & Analytics</title>

    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/dashboard.css">
    <link rel="stylesheet" href="../styles/logout_modal.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>

    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/session-check.js" defer></script>
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
            </div>
            <div class="filter-bar" style="background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <form id="dashboardFilterForm" style="display: flex; align-items: center; gap: 15px; width: 100%; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label for="dateFrom" style="font-weight: 500; font-size: 14px; color: #374151;">From:</label>
                        <input type="date" id="dateFrom" name="date_from" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label for="dateTo" style="font-weight: 500; font-size: 14px; color: #374151;">To:</label>
                        <input type="date" id="dateTo" name="date_to" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                    </div>
                    <div style="display: flex; gap: 10px; margin-left: auto;">
                        <button type="submit" style="padding: 8px 16px; background: #0D9488; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Apply Filter</button>
                        <button type="button" id="resetDashboardFilter" style="padding: 8px 16px; background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer; font-weight: 500;">Reset</button>
                    </div>
                </form>
            </div>
            <!-- OVERVIEW METRICS -->
            <div class="dashboard-tiles">

                <div class="tile tile-blue">
                    <div class="tile-info">
                        <span class="tile-label">Total Registered Patients</span>
                        <h2 class="tile-value" id="valTotalPatients">0</h2>
                    </div>
                    <div class="tile-icon"></div>
                </div>

                <div class="tile tile-orange">
                    <div class="tile-info">
                        <span class="tile-label">Waiting (Today)</span>
                        <h2 class="tile-value" id="valWaiting">0</h2>
                    </div>
                    <div class="tile-icon">
                        <img src="../svg/waiting.svg" alt="Waiting">
                    </div>
                </div>

                <div class="tile tile-lightblue">
                    <div class="tile-info">
                        <span class="tile-label">In Progress (Today)</span>
                        <h2 class="tile-value" id="valInProgress">0</h2>
                    </div>
                    <div class="tile-icon">
                        <img src="../svg/progress.svg" alt="In Progress">
                    </div>
                </div>

                <div class="tile tile-green">
                    <div class="tile-info">
                        <span class="tile-label">Completed (Today)</span>
                        <h2 class="tile-value" id="valCompleted">0</h2>
                    </div>
                    <div class="tile-icon">
                        <img src="../svg/completed.svg" alt="Completed">
                    </div>
                </div>

            </div>

            <!-- DEMOGRAPHICS SUMMARY -->
            <section class="demographics-section">

                <h2 class="dashboard-section-title">
                    Patient Demographics Summary
                </h2>

                <div class="demographics-grid">

                    <div class="demographic-card">
                        <h3>Civil Status Breakdown</h3>
                        <div id="civilStatusSummary"
                             class="demographic-breakdown"></div>
                        <div id="civilStatusTotal"
                             class="demographic-total">Total: 0</div>
                    </div>

                    <div class="demographic-card">
                        <h3>Gender Demographics</h3>
                        <div id="genderSummary"
                             class="demographic-breakdown"></div>
                        <div id="genderTotal"
                             class="demographic-total">Total: 0</div>
                    </div>

                    <div class="demographic-card">
                        <h3>Employment Status</h3>
                        <div id="employmentSummary"
                             class="demographic-breakdown"></div>
                        <div id="employmentTotal"
                             class="demographic-total">Total: 0</div>
                    </div>

                    <div class="demographic-card">
                        <h3>Age Group Breakdown</h3>
                        <div id="ageSummary"
                             class="demographic-breakdown"></div>
                        <div id="ageTotal"
                             class="demographic-total">Total: 0</div>
                    </div>

                    <div class="demographic-card">
                        <h3>PhilHealth Status</h3>

                        <div
                            id="philhealthSummary"
                            class="demographic-breakdown">
                        </div>

                        <div
                            id="philhealthTotal"
                            class="demographic-total">
                            Total: 0
                        </div>
                    </div>
                    <div class="demographic-card">
                        <h3>YAKAP Status</h3>

                        <div
                            id="yakapSummary"
                            class="demographic-breakdown">
                        </div>

                        <div
                            id="yakapTotal"
                            class="demographic-total">
                            Total: 0
                        </div>
                    </div>

                </div>
            </section>

            <!-- ANALYTICS GRAPHS -->
            <section>
                <h2 class="dashboard-section-title">
                    Analytics Graphs
                </h2>

                <div class="charts-grid">

                    <div class="content-card">
                        <h3>Age Distribution</h3>
                        <div class="chart-container">
                            <canvas id="ageDistributionChart"></canvas>
                        </div>
                        <div class="chart-metrics"
                             id="ageDistributionMetrics"></div>
                    </div>

                    <div class="content-card">
                        <h3>Civil Status</h3>
                        <div class="chart-container">
                            <canvas id="civilStatusChart"></canvas>
                        </div>
                        <div class="chart-metrics"
                             id="civilStatusMetrics"></div>
                    </div>

                    <div class="content-card">
                        <h3>Gender Distribution</h3>
                        <div class="chart-container">
                            <canvas id="genderDistributionChart"></canvas>
                        </div>
                        <div class="chart-metrics"
                             id="genderDistributionMetrics"></div>
                    </div>

                    <div class="content-card">
                        <h3>Employment Status</h3>
                        <div class="chart-container">
                            <canvas id="employmentStatusChart"></canvas>
                        </div>
                        <div class="chart-metrics"
                             id="employmentStatusMetrics"></div>
                    </div>

                    <div class="content-card chart-wide">
                        <h3>Service Distribution</h3>
                        <div class="chart-container chart-container-wide">
                            <canvas id="serviceDistributionChart"></canvas>
                        </div>
                        <div class="chart-metrics"
                             id="serviceDistributionMetrics"></div>
                    </div>

                </div>
            </section>
            <?php include '../pages/footer.php'; ?>
        </div>
    </div>
</body>
</html>
