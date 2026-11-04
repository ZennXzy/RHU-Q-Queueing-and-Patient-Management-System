
<?php
require '../api/check-private.php';
authorizeRoles(['Admin']);
require '../php/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>RHU-Q | Health Analytics</title>

    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/toast.css">
    <link rel="stylesheet" href="../styles/logout_modal.css">
    <link rel="stylesheet" href="../styles/reports_page.css">

    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/session-check.js" defer></script>
    <script src="../js/reports.js" defer></script>
</head>

<body>
<?php include '../pages/top-navbar.php'; ?>

<div class="workspace">
    <div class="toast-container" id="toastContainer"></div>

    <?php include '../pages/sidebar.php'; ?>

    <main class="content-area" id="analytics-main">

        <header class="analytics-header">
            <div>
                <h1 class="page-title" style="margin:0px;">Reports &amp; Health Analytics</h1>
            </div>

            <div class="header-actions">
                <button type="button" id="export-summary" class="secondary-btn">
                    Export Summary CSV
                </button>
                <button type="button" id="export-visits" class="primary-btn">
                    Export Visits CSV
                </button>
            </div>
        </header>

        <!-- STATISTICAL FILTERS -->
        <section class="panel filters">
            <div class="section-heading">
                <h2>Statistical Filters</h2>
                <button type="button" class="text-btn" id="reset-filters">
                    Reset filters
                </button>
            </div>

            <form id="report-filters">
                <div class="filter-grid">

                    <label>
                        Service
                        <select name="service" id="filter-service">
                            <option value="">All Services</option>
                        </select>
                    </label>

                    <label>
                        Barangay
                        <select name="barangay" id="filter-barangay">
                            <option value="">All Barangays</option>
                        </select>
                    </label>

                    <label>
                        Gender
                        <select name="gender" id="filter-gender">
                            <option value="">All Genders</option>
                        </select>
                    </label>

                    <label>
                        Employment
                        <select name="employment_status" id="filter-employment_status">
                            <option value="">All Employment</option>
                        </select>
                    </label>

                    <label>
                        Civil Status
                        <select name="civil_status" id="filter-civil_status">
                            <option value="">All Civil Status</option>
                        </select>
                    </label>

                    <label>
                        Religion
                        <select name="religion" id="filter-religion">
                            <option value="">All Religions</option>
                        </select>
                    </label>

                    <label>
                        PhilHealth
                        <select name="phil_status" id="filter-phil_status">
                            <option value="">All Statuses</option>
                        </select>
                    </label>

                    <label>
                        Yakap
                        <select name="yakap_member" id="filter-yakap_member">
                            <option value="">All</option>
                        </select>
                    </label>

                    <label>
                        Age From
                        <input type="number" min="0" max="120" name="age_min" placeholder="0">
                    </label>

                    <label>
                        Age To
                        <input type="number" min="0" max="120" name="age_max" placeholder="120">
                    </label>

                    <label>
                        Date From
                        <input type="date" name="date_from">
                    </label>

                    <label>
                        Date To
                        <input type="date" name="date_to">
                    </label>

                </div>

                <div class="filter-actions">
                    <span id="report-status" role="status" aria-live="polite">
                        Loading…
                    </span>

                    <button class="primary-btn" type="submit">
                        Apply Filters
                    </button>
                </div>
            </form>
        </section>

        <!-- SUMMARY CARDS -->
        <section class="kpi-grid" aria-label="Summary statistics">

            <article class="kpi">
                <span>Total Service Visits</span>
                <strong id="kpi-visits">—</strong>
                <small>Completed transactions</small>
            </article>

            <article class="kpi clickable-kpi" id="kpi-patients-card" role="button" tabindex="0" title="Click to view unique patients list">
                <span>Unique Patients</span>
                <strong id="kpi-patients">—</strong>
                <small>Distinct patients served (Click to view)</small>
            </article>

            <article class="kpi">
                <span>Barangays Covered</span>
                <strong id="kpi-barangays">—</strong>
                <small>With recorded service visits</small>
            </article>

            <article class="kpi">
                <span>Top Service</span>
                <strong id="kpi-service" class="kpi-text">—</strong>
                <small>By recorded visits</small>
            </article>

        </section>

        <!-- ANALYTICS CHARTS -->
        <section class="chart-grid">

            <article class="panel">
                <div class="section-heading">
                    <h2>Service Utilization</h2>
                    <span class="hint">Click a row to view patients</span>
                </div>

                <div id="service-chart" class="bar-chart"></div>
            </article>

            <article class="panel">
                <div class="section-heading">
                    <h2>Visits by Barangay</h2>
                    <span class="hint">Click a row to filter</span>
                </div>

                <div id="barangay-chart" class="bar-chart"></div>
            </article>

            <article class="panel">
                <div class="section-heading">
                    <h2>Demographic Overview</h2>

                    <select id="demographic-group" aria-label="Demographic category">
                        <option value="gender">Gender</option>
                        <option value="age">Age Group</option>
                        <option value="employment">Employment</option>
                        <option value="civil">Civil Status</option>
                        <option value="philhealth">PhilHealth</option>
                        <option value="yakap">Yakap</option>
                    </select>
                </div>

                <div id="demographic-chart" class="bar-chart"></div>

                <p class="hint">
                    Demographic bars show visits and unique patients for the selected category.
                </p>
            </article>

            <article class="panel">
                <div class="section-heading">
                    <h2>Monthly Activity</h2>
                    <span class="hint">Recorded service visits</span>
                </div>

                <div id="monthly-chart" class="bar-chart"></div>
            </article>

        </section>

        <!-- FILTERED PATIENT RECORDS -->
        <section class="panel records-panel" id="patient-records">

            <div class="section-heading">
                <div>
                    <h2>Matching Patient Visits</h2>
                    <p class="hint" id="records-count">
                        All filtered transactions
                    </p>
                </div>

                <div class="records-actions">
                    <input
                        id="searchReports"
                        type="search"
                        placeholder="Search patient, ID, queue…"
                        aria-label="Search records"
                    >

                    <button type="button" class="secondary-btn" id="export-patients">
                        Export Unique Patients CSV
                    </button>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Age / Gender</th>
                            <th>Service</th>
                            <th>Barangay</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="reports-table-body">
                        <tr>
                            <td colspan="6">Loading records…</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pager">
                <button id="prev-page" type="button" class="secondary-btn">
                    Previous
                </button>

                <span id="page-label">Page 1</span>

                <button id="next-page" type="button" class="secondary-btn">
                    Next
                </button>
            </div>

        </section>
        <!-- FILTERED UNIQUE PATIENTS LIST -->
        <section class="panel records-panel" id="unique-patients-panel">

            <div class="section-heading">
                <div>
                    <h2>Filtered Unique Patients</h2>
                    <p class="hint" id="unique-patients-count">
                        Distinct patients matching current filters
                    </p>
                </div>

                <div class="records-actions">
                    <input
                        id="searchUniquePatients"
                        type="search"
                        placeholder="Search unique patient name, ID…"
                        aria-label="Search unique patients"
                    >
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Patient Name</th>
                            <th>Age / Gender</th>
                            <th>Barangay</th>
                            <th>Total Visits</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="unique-patients-table-body">
                        <tr>
                            <td colspan="6">Loading unique patients…</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pager">
                <button id="unique-prev-page" type="button" class="secondary-btn">
                    Previous
                </button>

                <span id="unique-page-label">Page 1</span>

                <button id="unique-next-page" type="button" class="secondary-btn">
                    Next
                </button>
            </div>

        </section>

        <p class="disclaimer">
            Statistics reflect recorded RHU transactions, not disease prevalence
            or verified community coverage. Review population denominators and
            data completeness before planning outreach.
        </p>

    </main>
</div>

<!-- TRANSACTION DETAILS -->
<dialog id="view-record-modal" class="record-dialog">
    <div class="dialog-header">
        <h2>Transaction Details</h2>
        <button type="button" id="close-record" aria-label="Close">×</button>
    </div>

    <div id="recordDetailsContent"></div>
</dialog>
<!-- PATIENT TRANSACTION HISTORY MODAL -->
<dialog id="patient-history-modal" class="record-dialog">
    <div class="dialog-header">
        <h2 id="patientHistoryModalTitle">Patient Transaction History</h2>
        <button type="button" id="close-patient-history" aria-label="Close">×</button>
    </div>

    <div style="padding: 15px;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Queue Code</th>
                        <th>Service</th>
                        <th>Barangay</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="patient-history-tbody">
                    <tr>
                        <td colspan="5">Loading patient transactions...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</dialog>
</body>
</html>
