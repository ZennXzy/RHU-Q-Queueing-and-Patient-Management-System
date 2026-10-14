<?php
require_once '../api/check-private.php';
authorizeRoles(['Doctor/Staff']);
require_once '../php/connection.php';
$role = $_SESSION['logged_role'];
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
    <link rel="stylesheet" href="../styles/complete-queue-modal.css">
    <link rel="stylesheet" href="../styles/logout_modal.css">
    <link rel="stylesheet" href="../styles/toast.css">
    <link rel="stylesheet" href="../styles/queue_info_modal.css">
    
    <script src="../js/submenu.js" defer></script>
    <script src="../js/queueing-form.js" defer></script>
    <script src="../js/delete-queue.js" defer></script>
    <script scr="../js/display-settings-button.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script scr="../js/filterSelect.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/queue-list-loader.js" defer></script>
    <script src="../js/accept-queue.js" defer></script>
    <script src="../js/complete-queue.js" defer></script>
    <script src="../js/queue_info_modal.js" defer></script>
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
                </div>

                <!-- Current Patient Card -->
                <div class="form-grid">
                    <div class="in-progress-card" style="margin-bottom:15px;">
                        <h2 class="section-title">Current Patient</h2>
                            <div class="in-progress-scroll" id="in-progress-scroll" style="max-height:138px;">
                                <div class="empty-state" style="text-align: center; padding: 51px; color: #6b7280; font-size: 14px;"><p style="margin: 0;">No in-progress records found.</p>
                            </div>
                        </div>
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
                                        echo '<button type="button" class="btn-view-queue" ' .
                                        'data-id="' .htmlspecialchars($row['queue_info_id']). '" ' .
                                        'data-code="' .htmlspecialchars($row['queue_code']). '" ' .
                                        'data-name="' .htmlspecialchars($fullName). '" ' .
                                        'title="View queue information" aria-label="View queue information for ' .htmlspecialchars($fullName). '">' .
                                        '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' .
                                        '</button>';
                                        echo '<button type="button" class="btn-delete" ' .
                                        'data-id="' .htmlspecialchars($row['queue_info_id']). '" ' .
                                        'data-code="' .htmlspecialchars($row['queue_code']). '" ' .
                                        'data-name="' .htmlspecialchars($fullName). '"><img src="../svg/delete-icon.svg" alt="check-icon"></button>';
                                        echo '<button type="button" class="btn-accept" ' .
                                        'data-id="' .htmlspecialchars($row['queue_info_id']). '" ' .
                                        'data-code="' .htmlspecialchars($row['queue_code']). '" ' .
                                        'data-name="' .htmlspecialchars($fullName). '"><img src="../svg/check-icon.svg" alt="check-icon"></button>';
                                        echo        '</div>';
                                        echo    '</div>';
                                    }
                                } else {
                                    echo '<div class="empty-state" style="text-align: center; padding: 51px; color: #6b7280; font-size: 14px;">';
                                    echo '<p style="margin: 0;">No Waiting patient in records found.</p>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            ?> 
                        </div>
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
                                    <span class="stat-number">
                                    <?php
                                        $res = $conn->query("SELECT COUNT(*) AS completed_today_count FROM queue_information WHERE status = 'Completed' AND DATE(created_at) = CURDATE()");
                                        $row = $res->fetch_assoc();

                                        echo '<span class="stat-number" id="stat-number-completed">'.$row['completed_today_count'].'</span>';
                                    ?>
                                </div>
                                <?php if($role === 'Admin'){ ?>
                                <button type="button" class="add-queue-button" id="open-form-btn">
                                    <div class="add-button-items">
                                        <img src="../svg/add-queue.svg" alt="add-queue-icon" style="filter: brightness(0) invert(1)">
                                        Add Queue
                                    </div>
                                </button>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                </div>    
            </div>
        </div>
    </div>
    <!-- Hidden Receipt Template for Thermal Printer -->
    <div id="thermal-receipt-template" style="display: none;">
        <div class="receipt-container">
            <h3>RURAL HEALTH UNIT - ODIONGAN</h3>
            <p>RHU-Q Patient Management System</p>
            <hr>
            <h2>QUEUE CODE</h2>
            <h1 id="printQueueCode">A-001</h1>
            <hr>
            <p><strong>Patient:</strong> <span id="printPatientName">John Doe</span></p>
            <p><strong>Service:</strong> <span id="printServiceName">General Consultation</span></p>
            <p><strong>Date/Time:</strong> <span id="printDateTime">2026-10-13 01:00 PM</span></p>
            <hr>
            <p style="text-align: center;">Please wait for your number to be called.</p>
        </div>
    </div>
</body>
<!-- UPDATE: Add Patient Queue Dialog with Vitals and Complaints -->
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
                        <input type="text" id="patient-search" placeholder="Type to search patient..." autocomplete="off">
                        <input type="hidden" id="patient-id" name="patient_id" require_onced>
                        <div id="patient-search-results" class="search-results-dropdown" style="display: none;"></div>
                    </div>

                    <!-- Service Type Select Group -->
                    <div class="form-group">
                        <label for="service-type">Service Type</label>
                        <select class="service-type" id="service-type" name="service_name" require_onced>
                            <option value="" disabled selected>Select a service</option>
                            <option value="Medical Consultation">Medical Consultation</option>
                            <option value="Immunization">Immunization</option>
                            <option value="Pre-Natal">Pre-Natal</option>
                            <option value="Medical Laboratory">Medical Laboratory</option>
                            <option value="Family Planning">Family Planning</option>
                            <option value="Dental Examination/Tooth Extraction">Dental Examination/Tooth Extraction</option>
                            <option value="Availment of anti-Tuberculosis Drugs">Availment of anti-Tuberculosis Drugs</option>
                            <option value="Child Care services">Child Care services</option>
                            <option value="Availment of Tetanus Vaccine and Anti Rabies Vaccine">Availment of Tetanus Vaccine and Anti Rabies Vaccine</option>
                        </select>
                    </div>

                    <!-- Vitals Inputs Grid -->
                    <div class="vitals-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                        <div class="form-group">
                            <label for="blood-pressure">Blood Pressure (mmHg)</label>
                            <input type="text" id="blood-pressure" name="blood_pressure" placeholder="e.g. 120/80">
                        </div>
                        <div class="form-group">
                            <label for="respiratory-rate">Respiratory Rate (cpm)</label>
                            <input type="text" id="respiratory-rate" name="respiratory_rate" placeholder="e.g. 18">
                        </div>
                        <div class="form-group">
                            <label for="temperature">Temperature (°C)</label>
                            <input type="text" id="temperature" name="temperature" placeholder="e.g. 36.5">
                        </div>
                        <div class="form-group">
                            <label for="pulse-rate">Pulse Rate (bpm)</label>
                            <input type="text" id="pulse-rate" name="pulse_rate" placeholder="e.g. 72">
                        </div>
                        <div class="form-group">
                            <label for="weight">Weight (kg)</label>
                            <input type="text" id="weight" name="weight" placeholder="e.g. 60.5">
                        </div>
                        <div class="form-group">
                            <label for="height">Height (cm)</label>
                            <input type="text" id="height" name="height" placeholder="e.g. 165">
                        </div>
                    </div>

                    <!-- Complaints Field -->
                    <div class="form-group" style="margin-top: 10px;">
                        <label for="complaints">Chief Complaints / Reason for Visit</label>
                        <textarea id="complaints" name="complaints" rows="3" placeholder="Enter patient complaints..."></textarea>
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

<!-- NEW: 5-Second Queue Success Modal -->
<dialog id="queue-success-modal" class="complete-modal">
    <div class="modal-card" style="text-align: center; padding: 25px;">
        <h2 style="color: #0B695D; margin-bottom: 5px;">Patient Queued Successfully!</h2>
        <div style="font-size: 2.5rem; font-weight: bold; color: #0B695D; margin: 15px 0;" id="modalQueueCode">#0000</div>
        
        <div style="text-align: left; background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 15px; line-height: 1.8;">
            <p><strong>Patient Name:</strong> <span id="modalPatientName">--</span></p>
            <p><strong>Service:</strong> <span id="modalServiceName">--</span></p>
            <p><strong>Date & Time:</strong> <span id="modalDateTime">--</span></p>
        </div>

        <small style="color: #6b7280;">This window will automatically close in <span id="successCountdown">5</span>s...</small>
    </div>
</dialog>
<dialog id="delete-queue-modal" class="delete-modal">
    <div class="modal-card-delete">
        <div class="modal-header-delete">
            <h2>Confirm Deletion</h2>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to remove <strong id="deletePatientName"></strong> (<span id="deleteQueueCode"></span>) from the queue?</p>
            <small class="warning-text">This action cannot be undone.</small>
        </div>
        <div class="modal-actions">
            <button type="button" id="cancelDeleteBtn" class="btn-cancel">Cancel</button>
            <button type="button" id="confirmDeleteBtn" class="btn-danger">Delete</button>
        </div>
    </div>
</dialog>
<!-- html/complete-queue-modal.html -->
<dialog id="complete-queue-modal" class="complete-modal">
    <div class="modal-card">
        <div class="modal-header-complete">
            <h2>Complete Queue</h2>
            <span id="completeQueueCodeBadge" class="queue-badge">QUEUE: #0000</span>
        </div>

        <form id="completeQueueForm" method="dialog">
            <input type="hidden" id="completeQueueId" name="queue_info_id">
            <input type="hidden" id="completePatientNameInput" name="patient_name">
            <input type="hidden" id="completeQueueCodeInput" name="queue_code">
            <input type="hidden" id="completeServiceInput" name="service">

            <div class="formcontainer">
                <div class="mainform">
                    <p class="patient-info-label">Patient: <strong id="completePatientName">--</strong></p>
                    <p class="service-info-label">Service: <strong id="serviceName">--</strong></p>

                    <div class="form-group">
                        <label for="completeRemarks">Remarks / Notes</label>
                        <textarea id="completeRemarks" name="remarks" rows="3" placeholder="Enter consultation or queue remarks..." require_onced></textarea>
                    </div>

                    <div class="form-group switch-group">
                        <label for="toggleAppointment" class="switch-label">
                            <span>Add Follow-up Appointment</span>
                            <input type="checkbox" id="toggleAppointment" name="add_appointment" value="1">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div id="appointmentContainer" class="appointment-container hidden">
                        <div class="form-group">
                            <label for="appointmentDate">Select Appointment Date</label>
                            <input type="date" id="appointmentDate" name="appointment_date" min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="cancelCompleteBtn" class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-submit">Complete</button>
                </div>
            </div>
        </form>
    </div>
</dialog>
<!-- Queue Information modal -->
<dialog id="queueInfoModal" class="qinfo-modal" aria-labelledby="qiTitle">
    <div class="qinfo-header">
        <div class="qinfo-header__text">
            <h2 class="qinfo-title" id="qiTitle">Queue Information</h2>
            <span class="qinfo-code" id="qiQueueCode"></span>
        </div>
        <button type="button" class="qinfo-close" id="qiCloseBtn" aria-label="Close">&times;</button>
    </div>

    <div class="qinfo-body">
        <p class="qinfo-status" id="qiStatus" role="status" hidden></p>

        <div class="qinfo-content" id="qiContent">
            <section class="qinfo-patient">
                <h3 class="qinfo-patient__name" id="qiPatient">--</h3>
                <dl class="qinfo-meta">
                    <div>
                        <dt>Service</dt>
                        <dd id="qiService">--</dd>
                    </div>
                    <div>
                        <dt>Date and time</dt>
                        <dd id="qiDate">--</dd>
                    </div>
                </dl>
            </section>

            <section>
                <h3 class="qinfo-section-title">Vital signs</h3>
                <div class="qinfo-vitals">
                    <div class="qinfo-tile" data-vital="blood_pressure">
                        <span class="qinfo-tile__label">Blood pressure</span>
                        <span class="qinfo-tile__value">--</span>
                    </div>
                    <div class="qinfo-tile" data-vital="respiratory_rate">
                        <span class="qinfo-tile__label">Respiratory rate</span>
                        <span class="qinfo-tile__value">--</span>
                    </div>
                    <div class="qinfo-tile" data-vital="temperature">
                        <span class="qinfo-tile__label">Temperature</span>
                        <span class="qinfo-tile__value">--</span>
                    </div>
                    <div class="qinfo-tile" data-vital="pulse_rate">
                        <span class="qinfo-tile__label">Pulse rate</span>
                        <span class="qinfo-tile__value">--</span>
                    </div>
                    <div class="qinfo-tile" data-vital="weight">
                        <span class="qinfo-tile__label">Weight</span>
                        <span class="qinfo-tile__value">--</span>
                    </div>
                    <div class="qinfo-tile" data-vital="height">
                        <span class="qinfo-tile__label">Height</span>
                        <span class="qinfo-tile__value">--</span>
                    </div>
                </div>
            </section>

            <section>
                <h3 class="qinfo-section-title">Diagnosis</h3>
                <p class="qinfo-diagnosis" id="qiDiagnosis">--</p>
            </section>
        </div>
    </div>

    <div class="qinfo-footer">
        <button type="button" class="qinfo-btn" id="qiFooterCloseBtn">Close</button>
    </div>
</dialog>
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

</script>
</html>