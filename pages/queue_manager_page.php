<?php
  require '../php/connection.php';
  require '../php/check-private.php';
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

</head>
<body>

    <!-- Top Navigation Header -->
    <div class="top-navbar">
        <div style="display:flex; flex-direction:row; align-items: center;">
            <img src="../images/rhu_logo_topNav.png" alt="rhu_logo2">
            <h3>RHU-Q: Queuing and Patient Management System</h3>
        </div>
        <div class="rightNav">
            <h3>Hello 
                <?php
                    $username = $_SESSION['logged_name'];
                    echo ucfirst($username);
                ?>
                !
            </h3>
        </div>
    </div>

    <div class="workspace">
        <div class="toast-container" id="toastContainer"></div>
        <div class="sidebar">
            <div class="nav-group">
                <a href="../pages/register_page.php" class="menu-item">
                    <div class="menu-content">
                        <div class="menu-icon">
                            <img src="../svg/registration-paper.svg" alt="registration_icon">
                        </div>
                        <span class="menu-text">Registration</span>
                    </div>
                </a>
                <div class="dropdown-group open">
                    <a href="javascript:void(0)" class="menu-item dropdown-toggle" onclick="toggleDropdown(this)">
                        <div class="menu-content">
                            <div class="menu-icon">
                                <img src="../svg/queue-line.svg" alt="queue_icon">
                            </div>
                            <span class="menu-text">Queue</span>
                        </div>
                        <div class="dropdown-arrow">
                            <svg viewBox="0 0 24 24" width="14" height="14">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </a> 
                    <div class="submenu-container">
                        <a href="../pages/queue_display_page.php" class="sub-nav-link">
                            <div class="menu-icon">         
                                <img src="../svg/queue-signal.svg" alt="queue_display_icon">
                            </div>
                            <span class="menu-text">Queue Display</span>
                        </a> 
                        
                        <a href="../pages/queue_manager_page.php" class="sub-nav-link active">
                            <div class="menu-icon">
                                <img src="../svg/queue-manager.svg" alt="queue-manager-icon">
                            </div>
                            <span class="menu-text">Queue Manager</span> 
                        </a>
                        <a href="../pages/display_settings.php" class="sub-nav-link">
                            <div class="menu-icon">
                                <img src="../svg/display-settings-icon.svg" alt="queue-manager-icon">
                            </div>
                            <span class="menu-text">Edit Display Cards</span> 
                        </a>
                    </div>
                </div>

                <a href="../pages/appointment_page.php" class="menu-item">
                    <div class="menu-content">
                        <div class="menu-icon">
                            <img src="../svg/reminder-appointment.svg" alt="appointment-icon">
                        </div>
                        <span class="menu-text">Appointment</span>
                    </div>
                </a>
                <a href="#" class="menu-item">
                    <div class="menu-content">
                        <div class="menu-icon">
                            <img src="../svg/dashboard-monitor.svg" alt="dashboard_icon">
                        </div>
                        <span class="menu-text">Dashboard</span>
                    </div>
                </a>
                <a href="#" class="menu-item">
                    <div class="menu-content">
                        <div class="menu-icon">
                            <img src="../svg/reports.svg" alt="reports-icon">
                        </div>
                        <span class="menu-text">Reports</span>
                    </div>
                </a>

                <a href="../pages/patient_records.php" class="menu-item">
                    <div class="menu-content">
                        <div class="menu-icon">
                            <img src="../svg/records.svg" alt="records-icon">
                        </div>
                        <span class="menu-text">Patient Records</span>
                    </div>
                </a>
            </div>

            <div class="lowercontrol">
                <a href="../pages/accounts_page.php" class="menu-item account-item" style="width:200px; padding-left: 0px;">
                    <div class="menu-content" style="justify-content: center;">
                        <div class="menu-icon">
                            <img src="../svg/account.svg" alt="account-icon">
                        </div>
                        <span class="menu-text">Account</span>
                    </div>
                </a>
                <form action="../php/logout.php" method="post" class="menu-item logout-item" id="logoutForm" style="transform: rotate(180deg);">
                    <div class="menu-content">
                        <div class="menu-icon">
                            <button type="submit" name="logout" style="border:none; background:none;">
                                <img src="../svg/logout-icon.svg" alt="logout-icon">
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

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

                            echo '<div class="in-progress-scroll" style="max-height:138px;">';

                            if($res && $res->num_rows > 0){
                                while ($row = $res->fetch_assoc()){
                                    $fullName = ucfirst($row['lastname']) . ', ' . ucfirst($row['firstname']).' '.ucfirst($row['suffix']);
                                    echo '<div class="patient-row current-patient-row">';
                                    echo '<span class="queue-id">'.htmlspecialchars($row['queue_code']).'</span>';
                                    echo '<span class="patient-name">'.htmlspecialchars($fullName).'</span>';
                                    echo '<span class="service-type">'.htmlspecialchars($row['service_name']).'</span>';
                                    echo '<div class="action-container">';
                                    echo        '<span class="more-options">⋮</span>';
                                    echo        '<button class="btn-complete">✔ Complete</button>';
                                    echo        '</div>';
                                    echo    '</div>';
                                }
                            } else {
                                echo '<div class="empty-state" style="text-align: center; padding: 51px; color: #6b7280; font-size: 14px;">';
                                echo '  <p style="margin: 0;">No in-progress records found.</p>';
                                echo '</div>';
                            }
                            echo '</div>';
                        ?> 
                    </div>

                    <!-- Split Section: Waiting Queue & Dashboard Stats -->
                    <div class="split-grid">
                        
                        <!-- Waiting Queue Card -->
                        <div class="section-card">
                            <h2 class="section-title">Waiting Queue</h2>
                            <div class="scrollable-card">
                                <div class="patient-row waiting-patient-row">
                                    <span class="queue-id">A0014</span>
                                    <span class="patient-name">Castro, Jose N</span>
                                    <span class="service-type"></span>
                                    <button class="btn-accept">Accept</button>
                                </div>

                                <div class="patient-row waiting-patient-row">
                                    <span class="queue-id">A0015</span>
                                    <span class="patient-name">Cruz, Carlos I</span>
                                    <span class="service-type"></span>
                                    <button class="btn-accept">Accept</button>
                                </div>

                                <div class="patient-row waiting-patient-row">
                                    <span class="queue-id">A0016</span>
                                    <span class="patient-name">Ramos, Teresa C</span>
                                    <span class="service-type"></span>
                                    <button class="btn-accept">Accept</button>
                                </div>

                                <div class="patient-row waiting-patient-row">
                                    <span class="queue-id">C0017</span>
                                    <span class="patient-name">Gonzales, Beatriz L</span>
                                    <span class="service-type"></span>
                                    <button class="btn-accept">Accept</button>
                                </div>

                                <div class="patient-row waiting-patient-row">
                                    <span class="queue-id">C0018</span>
                                    <span class="patient-name">Torres, Fernando O</span>
                                    <span class="service-type"></span>
                                    <button class="btn-accept">Accept</button>
                                </div>
                                <div class="patient-row waiting-patient-row">
                                    <span class="queue-id">A0014</span>
                                    <span class="patient-name">Castro, Jose N</span>
                                    <span class="service-type"></span>
                                    <button class="btn-accept">Accept</button>
                                </div>

                                <div class="patient-row waiting-patient-row">
                                    <span class="queue-id">A0015</span>
                                    <span class="patient-name">Cruz, Carlos I</span>
                                    <span class="service-type"></span>
                                    <button class="btn-accept">Accept</button>
                                </div>

                                <div class="patient-row waiting-patient-row">
                                    <span class="queue-id">A0016</span>
                                    <span class="patient-name">Ramos, Teresa C</span>
                                    <span class="service-type"></span>
                                    <button class="btn-accept">Accept</button>
                                </div>

                                <div class="patient-row waiting-patient-row">
                                    <span class="queue-id">C0017</span>
                                    <span class="patient-name">Gonzales, Beatriz L</span>
                                    <span class="service-type"></span>
                                    <button class="btn-accept">Accept</button>
                                </div>

                                <div class="patient-row waiting-patient-row">
                                    <span class="queue-id">C0018</span>
                                    <span class="patient-name">Torres, Fernando O</span>
                                    <span class="service-type"></span>
                                    <button class="btn-accept">Accept</button>
                                </div>
                            </div>
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
                                    <span class="stat-number"><?php
                                        $res = $conn->query("SELECT COUNT(*) AS waiting_count FROM queue_information WHERE status = 'Waiting'");
                                        $row = $res->fetch_assoc();
                                        echo $row['waiting_count'];
                                    ?></span>
                                </div>

                                <!-- In Progress Stat -->
                                <div class="stat-card in-progress">
                                    <div class="stat-info">
                                        <img src="../svg/progress.svg" alt="progress-icon" style="filter: brightness(0) saturate(100%)
                                        invert(28%) sepia(88%) saturate(1097%)
                                        hue-rotate(186deg) brightness(91%) contrast(93%);">
                                        <span class="stat-label">In Progress</span>
                                    </div>
                                    <span class="stat-number"><?php
                                        $res = $conn->query("SELECT COUNT(*) AS in_progress_count FROM queue_information WHERE status = 'In Progress'");
                                        $row = $res->fetch_assoc();
                                        echo $row['in_progress_count'];
                                    ?></span>
                                </div>

                                <!-- Completed Today Stat -->
                                <div class="stat-card completed">
                                    <div class="stat-info">
                                        <img src="../svg/completed.svg" alt="completed-icon" style="filter: brightness(0) saturate(100%)
                                        invert(38%) sepia(69%) saturate(572%)
                                        hue-rotate(75deg) brightness(91%) contrast(88%);">
                                        <span class="stat-label">Completed Today</span>
                                    </div>
                                    <span class="stat-number"><?php
                                        $res = $conn->query("SELECT COUNT(*) AS completed_today_count FROM queue_information WHERE status = 'Completed' AND DATE(created_at) = CURDATE()");
                                        $row = $res->fetch_assoc();
                                        echo $row['completed_today_count'];
                                    ?></span>
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
            <div id="settings-display" class="settings-display">
                <div class="settings-content">

                </div>
            </div>
        </div>

        <!-- popupform -->
    
        <div id="queue-form" class="queue-form">
            <div class="modal-card">
                <h2>Add Patient to Queue</h2>
                <form id="add-queue-form">
                    <div class="formcontainer">
                        <div class="gridrow">
                            <div class="gridcolumn">
                                <div class="mainform">
                                    <div class="search-container">
                                        <div class="search-icon">
                                            <img src="../svg/search-icon.svg" alt="search-icon">
                                        </div>
                                        <input type="text" placeholder="Search by name">
                                    </div>
                                    <div class="form-group">
                                        <label for="patient-name">Patient Name</label>
                                        <input type="text" id="patient-name" placeholder="Enter full name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="service-type">Service Type</label>
                                        <input type="text" id="service-type" placeholder="e.g. Medical Consultation" required>
                                    </div>
                                </div>
                            </div><div class="gridcolumn">
                                <div class="subform">
                                    <div class="search-container">
                                        <div class="search-icon">
                                            <img src="../svg/search-icon.svg" alt="search-icon">
                                        </div>
                                        <input type="text" placeholder="Search by name">
                                    </div>
                                    <div class="form-group">
                                        <label for="patient-name">Patient Name</label>
                                        <input type="text" id="patient-name" placeholder="Enter full name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="service-type">Service Type</label>
                                        <input type="text" id="service-type" placeholder="e.g. Medical Consultation" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" id="closeBtn" class="btn-cancel">Cancel</button>
                            <button type="submit" class="btn-submit">Add</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</body>

<script>
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