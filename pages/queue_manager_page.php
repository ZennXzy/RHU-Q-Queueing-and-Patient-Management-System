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
    <title>RHU-Q: Queue Manager</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/queue_manager_page.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/queue-forms.css">
    <link rel="stylesheet" href="../styles/toast.css">
    
    <script src="../js/submenu.js" defer></script>
    <script src="../js/queueing-form.js" defer></script>
    <script scr="../js/display-settings-button.js" defer></script>
    <script scr="../js/filterSelect.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/toast.js" defer></script>
    <!-- <script src="../js/queue-list-loader.js" defer></script> -->
    <script src="../js/accept-queue.js" defer></script>
    <script src="../js/complete-queue.js" defer></script>
    <script src="../js/session-check.js" defer></script>


</head>
<body>
    <?php
        include '../pages/top-navbar.php';
    ?>

    <div class="workspace">
        <div class="toast-container" id="toastContainer"></div>
        <?php
            include '../pages/sidebar.php';
        ?>

        <!-- Main Content Area -->
        <div class="content-area">

            <div id="main-display" class="main-display active">
                <!-- Page Title Header -->
                <div class="content-header">
                    <h1 class="page-title">Queue Manager</h1>
                    <div id="filterSelector" class="filterSelector">
                        <img src="../svg/bars-filter.svg" alt="filter-icon">
                        <div id="filterSelect" class="filterSelect">
                            <option value="medical">Medical</option>
                            <option value="dental">Dental</option>
                            <option value="vaccine">Vaccine</option>
                            <option value="laboratory">Laboratory</option>
                        </div>
                    </div>
                </div>

                <!-- Current Patient Card -->
                <div class="form-grid">
                    <div class="in-progress-card" style="margin-bottom:15px;">
                        <h2 class="section-title">Current Patient</h2>
                        <?php
                            $sql = "SELECT 
                                        q.queue_info_id, 
                                        q.queue_code, 
                                        q.patient_id, 
                                        q.service_name, 
                                        q.created_at,
                                        p.firstname, 
                                        p.lastname,
                                        p.suffix
                                    FROM queue_information q
                                    INNER JOIN patient_records p ON q.patient_id = p.patient_id
                                    WHERE q.status = 'In Progress' 
                                    ORDER BY q.created_at ASC";

                            $res = $conn->query($sql);

                            echo '<div class="in-progress-scroll" id="in-progress-scroll" style="max-height:138px;">';

                            if ($res && $res->num_rows > 0) {
                                while ($row = $res->fetch_assoc()) {
                                    $suffix   = !empty($row['suffix']) ? ' ' . ucfirst($row['suffix']) : '';
                                    $fullName = ucfirst($row['lastname']) . ', ' . ucfirst($row['firstname']) . $suffix;

                                    echo '<div class="patient-row current-patient-row">';
                                    echo '  <span class="queue-id">' . htmlspecialchars($row['queue_code']) . '</span>';
                                    echo '  <span class="patient-name">' . htmlspecialchars($fullName) . '</span>';
                                    echo '  <span class="service-type">' . htmlspecialchars($row['service_name']) . '</span>';
                                    echo '  <div class="action-container">';
                                    echo '    <span class="more-options">⋮</span>';
                                    echo '    <button type="button" class="btn-complete" ';
                                    echo '      data-queue-id="' . htmlspecialchars($row['queue_info_id']) . '" ';
                                    echo '      data-patient-name="' . htmlspecialchars($fullName) . '" ';
                                    echo '      data-queue-code="' . htmlspecialchars($row['queue_code']) . '" ';
                                    echo '      data-service="' . htmlspecialchars($row['service_name']) . '" ';
                                    echo '      data-accepted-at="' . htmlspecialchars($row['created_at']) . '">✔ Complete</button>';
                                    echo '  </div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="empty-state" style="text-align: center; padding: 51px; color: #6b7280; font-size: 14px;">';
                                echo '  <p style="margin: 0;">No in-progress records found.</p>';
                                echo '</div>';
                            }

                            echo '</div>';
                        ?> 
                    </div>

                    <div class="split-grid">
                        
                        <div class="waiting-card">
                            <h2 class="section-title">Waiting Queue</h2>
                            <?php
                                $sql = "SELECT 
                                            q.queue_info_id, 
                                            q.queue_code, 
                                            q.patient_id, 
                                            q.service_name, 
                                            q.created_at,
                                            p.firstname, 
                                            p.lastname,
                                            p.suffix
                                        FROM queue_information q
                                        INNER JOIN patient_records p ON q.patient_id = p.patient_id
                                        WHERE q.status = 'Waiting' 
                                        ORDER BY q.created_at ASC";

                                $res = $conn->query($sql);
                                
                                echo '<div class="waiting-card-scroll" id="waiting-card-scroll">';

                                if($res && $res->num_rows > 0){
                                    while ($row = $res->fetch_assoc()){
                                        $fullName = ucfirst($row['lastname']) . ', ' . ucfirst($row['firstname']).' '.ucfirst($row['suffix']);
                                        echo '<div class="patient-row waiting-patient-row">';
                                        echo '<span class="queue-id">'.htmlspecialchars($row['queue_code']).'</span>';
                                        echo '<span class="patient-name">'.htmlspecialchars($fullName).'</span>';
                                        echo '<span class="service-type">'.htmlspecialchars($row['service_name']).'</span>';
                                        echo '<div class="action-container">';
                                        echo        '<span class="more-options">⋮</span>';
                                        echo '<button type="button" class="btn-accept" ' .
                                        'data-id="' .htmlspecialchars($row['queue_info_id']). '" ' .
                                        'data-code="' .htmlspecialchars($row['queue_code']). '" ' .
                                        'data-name="' .htmlspecialchars($fullName). '"><img src="../svg/check-icon.svg" alt="check-icon"></button>';
                                        echo        '</div>';
                                        echo    '</div>';
                                    }
                                } else {
                                    echo '<div class="empty-state" style="text-align: center; padding: 51px; color: #6b7280; font-size: 14px;">';
                                    echo '  <p style="margin: 0;">No Waiting patient in records found.</p>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            ?> 
                        </div>

                        <!-- Dashboard Counters Column -->
                        <div>
                            <h2 class="section-title" style="margin-bottom: 15px; padding-bottom: 0px; padding-top: 15px;">Dashboard</h2>
                            
                            <div class="dashboard-container">
                                <!-- Waiting Stat -->
                                <div class="stat-card waiting">
                                    <div class="stat-info">
                                        <img src="../svg/waiting.svg" alt="waiting-icon" style="filter: brightness(0) saturate(100%) invert(52%) sepia(96%)
                                        saturate(1536%) hue-rotate(7deg) brightness(98%) contrast(101%);">
                                        <span class="stat-label">Waiting</span>
                                    </div>
                                    <?php
                                        $res = $conn->query("SELECT COUNT(*) AS waiting_count FROM queue_information WHERE status = 'Waiting'");
                                        $row = $res->fetch_assoc();

                                        echo '<span class="stat-number" id="stat-number-waiting">';
                                        echo $row['waiting_count'];
                                        echo'</span>';
                                    ?>
                                </div>

                                <!-- In Progress Stat -->
                                <div class="stat-card in-progress">
                                    <div class="stat-info">
                                        <img src="../svg/progress.svg" alt="progress-icon" style="filter: brightness(0) saturate(100%)
                                        invert(28%) sepia(88%) saturate(1097%)
                                        hue-rotate(186deg) brightness(91%) contrast(93%);">
                                        <span class="stat-label">In Progress</span>
                                    </div>
                                    <?php
                                        $res = $conn->query("SELECT COUNT(*) AS in_progress_count FROM queue_information WHERE status = 'In Progress'");
                                        $row = $res->fetch_assoc();

                                        echo '<span class="stat-number" id="stat-number-in-progress">'.$row['in_progress_count'].'</span>';
                                    ?>
                                </div>

                                <!-- Completed Today Stat -->
                                <div class="stat-card completed">
                                    <div class="stat-info">
                                        <img src="../svg/completed.svg" alt="completed-icon" style="filter: brightness(0) saturate(100%)
                                        invert(38%) sepia(69%) saturate(572%)
                                        hue-rotate(75deg) brightness(91%) contrast(88%);">
                                        <span class="stat-label">Completed Today</span>
                                    </div>
                                    <?php
                                        $res = $conn->query("SELECT COUNT(*) AS completed_today_count FROM queue_information WHERE status = 'Completed' AND DATE(created_at) = CURDATE()");
                                        $row = $res->fetch_assoc();

                                        echo '<span class="stat-number" id="stat-number-completed">'.$row['completed_today_count'].'</span>';
                                    ?>
                                </div>
                                <button type="button" class="add-queue-button" id="open-form-btn">
                                    <div class="add-button-items">
                                        <img src="../svg/add-queue.svg" alt="add-queue-icon" style="filter: brightness(0) invert(1)">
                                        Add Queue
                                    </div>
                                </button> 
                            </div>
                        </div>
                    </div>
                </div>    
            </div>
        </div>

        <dialog id="queue-form" class="queue-form">
            <div class="modal-card" id="modal-card">
                <div class="toast-container" id="toastContainer"></div>
                <h2>Add Patient to Queue</h2>
                <form id="add-queue-form" method="dialog">
                    <div class="formcontainer">
                        <div class="mainform">
                            
                            <!-- Patient Search Group -->
                            <div class="form-group" style="position: relative;">
                                <label for="patient-search">Patient Name</label>
                                <input type="text" id="patient-search" placeholder="Type to search patient..." autocomplete="off" required>
                                <!-- Hidden input to store selected patient ID -->
                                <input type="hidden" id="patient-id" name="patient_id" required>
                                <!-- Dropdown list for search results -->
                                <div id="patient-search-results" class="search-results-dropdown" style="display: none;"></div>
                            </div>

                            <!-- Service Type Select Group -->
                            <div class="form-group">
                                <label for="service-type">Service Type</label>
                                <select class="service-type" id="service-type" name="service_name" required>
                                    <option value="" disabled selected>Select a service</option>
                                    <option value="Medical Consultation">Medical Consultation</option>
                                    <option value="Prenatal Checkup">Prenatal Checkup</option>
                                    <option value="Immunization / Vaccination">Immunization / Vaccination</option>
                                    <option value="Dental Checkup">Dental Checkup</option>
                                    <option value="Laboratory Request">Laboratory Request</option>
                                </select>
                            </div>

                        </div>
                        <div class="form-actions">
                            <button type="button" id="closeBtn" class="btn-cancel" onclick="document.getElementById('queue-form').close()">Cancel</button>
                            <button type="submit" class="btn-submit">Add</button>
                        </div>
                    </div>
                </form>
            </div>
        </dialog>
    </div>
    
</body>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        const type = urlParams.get('type') || 'info';

        if (msg) {
            showToast(msg, type);

            // Clean query parameters from the address bar
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    });

    const dropdown = document.getElementById('filterSelect');
    const filterBtn = document.getElementById('filterSelector');

    filterBtn.addEventListener('click', () => {
        if(dropdown.classList.contains('active')){
            dropdown.classList.remove('active');
            filterBtn.classList.remove('active');
        } else {
            dropdown.classList.add('active');
            filterBtn.classList.add('active');
        }
    });
</script>
</html>