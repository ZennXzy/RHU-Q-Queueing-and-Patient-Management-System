<?php
session_start();
require_once '../api/check-private.php';
authorizeRoles(['Doctor/Staff', 'Display', 'Patient']);
require_once '../php/connection.php';
$role = $_SESSION['logged_role'];
$currentPage = 'queue_display';

$DEPARTMENT_CONFIG = [
    'Medical Consultation' => ['title' => 'MEDICAL CONSULTATION', 'themeClass' => 'dept-medical', 'icon' => '../svg/medical-icon.svg'],
    'Pre-Natal'     => ['title' => 'PRENATAL CHECKUP',     'themeClass' => 'dept-medical', 'icon' => '../svg/prenatal-icon.svg'],
    'Immunization' => ['title' => 'IMMUNIZATION',    'themeClass' => 'dept-vaccine', 'icon' => '../svg/vaccine-icon.svg'],
    'Dental Examination/Tooth Extraction'       => ['title' => 'DENTAL CHECKUP',       'themeClass' => 'dept-dental',  'icon' => '../svg/dental-icon.svg'],
    'Medical Laboratory'   => ['title' => 'LABORATORY',           'themeClass' => 'dept-medical', 'icon' => '../svg/laboratory-icon.svg'],
    'Family Planning'      => ['title' => 'FAMILY PLANNING',      'themeClass' => 'dept-medical', 'icon' => '../svg/medical-icon.svg'],
    'Availment of anti-Tuberculosis Drugs' => ['title' => 'ANTI-TB DRUGS', 'themeClass' => 'dept-medical', 'icon' => '../svg/medical-icon.svg'],
    'Child Care services'  => ['title' => 'CHILD CARE',           'themeClass' => 'dept-vaccine', 'icon' => '../svg/vaccine-icon.svg'],
    'Availment of Tetanus Vaccine and Anti Rabies Vaccine' => ['title' => 'TETANUS & RABIES VACCINE', 'themeClass' => 'dept-vaccine', 'icon' => '../svg/vaccine-icon.svg']
];

// Fetch active services configured in DB
$activeServices = [];
$settingsQuery = $conn->query("SELECT service_type FROM queue_display WHERE status = 'Active'");
if ($settingsQuery) {
    while ($sRow = $settingsQuery->fetch_assoc()) {
        $activeServices[] = $sRow['service_type'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: Queue Display</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/queue_display.css">
    <?php
        if ($role === 'Patient'){
        echo '<link rel="stylesheet" href="../styles/patient_sidebar.css">';
        echo '<script src="../js/patient_sidebar.js" defer></script>';
        }
    ?>

    <script src="../js/logout.js" defer></script>
    <script src="../js/patient_notification.js" defer></script>

</head>
<body>
    <div class="page-container">
        <?php
            if ($role == 'Patient'){
                include '../pages/patient_queue_aside.php';
            }
        ?>
        <div class="top-brand" >
            <div class="rhu_logo_topNav">
                <?php
                    if ($role == 'Patient'){
                        include '../pages/patient_sidebar.php';
                    }
                ?>
                <img src="../images/rhu_logo_topNav.png" alt="rhu_logo">
            </div> 
            <div style="text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);">
                RHU-Q: Queuing and Patient Management System
            </div>
        </div>

        <h1 class="page-title">Queue Display</h1>
        <div class="display-card grid-dynamic">
            <div id="no-services-msg" class="no-services-message" style="display: <?php echo empty($activeServices) ? 'flex' : 'none'; ?>;">
                <h3>No Selected Services</h3>
                <p>Please select at least one service card from the display settings.</p>
            </div>
            
            <?php foreach ($DEPARTMENT_CONFIG as $serviceName => $config): 
                $isVisible = in_array($serviceName, $activeServices);

                // Now serving
                $sqlActive = "SELECT queue_code FROM queue_information WHERE service_name = ? AND status = 'In Progress' ORDER BY created_at ASC";
                $stmtActive = $conn->prepare($sqlActive);
                $stmtActive->bind_param("s", $serviceName);
                $stmtActive->execute();
                $resActive = $stmtActive->get_result();

                // Waiting
                $sqlWaiting = "SELECT queue_code FROM queue_information WHERE service_name = ? AND status = 'Waiting' ORDER BY created_at ASC";
                $stmtWaiting = $conn->prepare($sqlWaiting);
                $stmtWaiting->bind_param("s", $serviceName);
                $stmtWaiting->execute();
                $resWaiting = $stmtWaiting->get_result();

                // Average waiting time (today)
                $sql = "SELECT service_duration FROM transactions WHERE service = ? AND DATE(transaction_date) = CURDATE() AND service_duration IS NOT NULL";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $serviceName);
                $stmt->execute();
                $result = $stmt->get_result();

                $totalSeconds = 0;
                $count = 0;
                while ($row = $result->fetch_assoc()) {
                    preg_match_all('/\d+/', $row['service_duration'], $matches);
                    if (!empty($matches[0]) && count($matches[0]) >= 3) {
                        $totalSeconds += ((int)$matches[0][0] * 86400) + ((int)$matches[0][1] * 3600) + ((int)$matches[0][2] * 60);
                        $count++;
                    }
                }

                $avgFormatted = "00:00:00";
                if ($count > 0) {
                    $avgSeconds = (int) round($totalSeconds / $count);
                    $avgFormatted = sprintf('%02d:%02d:%02d', floor($avgSeconds / 3600), floor(($avgSeconds % 3600) / 60), $avgSeconds % 60);
                }
                $stmtActive->close();
                $stmtWaiting->close();
                $stmt->close();
            ?>

                <div class="dept-column" data-service="<?php echo htmlspecialchars($serviceName); ?>" style="display: <?php echo $isVisible ? 'flex' : 'none'; ?>;">
                    <div class="queue-card <?php echo $config['themeClass']; ?>">
                        <div class="dept-title">
                            <img src="<?php echo $config['icon']; ?>" alt="<?php echo $config['title']; ?>"> 
                            <?php echo htmlspecialchars($config['title']); ?>
                        </div>

                        <!-- LEFT: Waiting -->
                        <div class="queue-col queue-col-waiting">
                            <div class="queue-col-header">Waiting</div>
                            <div class="ticket-box-container waiting-list">
                                <?php if ($resWaiting && $resWaiting->num_rows > 0): ?>
                                    <?php while ($waitingRow = $resWaiting->fetch_assoc()): ?>
                                        <div class="ticket-box is-waiting"><?php echo htmlspecialchars($waitingRow['queue_code']); ?></div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="no-queue">No Waiting</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- RIGHT: Now Serving -->
                        <div class="queue-col queue-col-serving">
                            <div class="queue-col-header">Now Serving</div>
                            <div class="ticket-box-container serving-list">
                                <?php if ($resActive && $resActive->num_rows > 0): ?>
                                    <?php while ($activeRow = $resActive->fetch_assoc()): ?>
                                        <div class="ticket-box"><?php echo htmlspecialchars($activeRow['queue_code']); ?></div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="no-queue">No Queue</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="time-stat">
                            <div class="label">Average Waiting Time</div>
                            <div class="value avg-time-val"><?php echo $avgFormatted; ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            async function fetchLiveQueueData() {
                try {
                    const response = await fetch('../api/get_queue_display_data.php');
                    const result = await response.json();

                    if (result.status === 'success' && Array.isArray(result.data)) {
                        let visibleCount = 0;

                        result.data.forEach(item => {
                            const col = document.querySelector(`.dept-column[data-service="${CSS.escape(item.service)}"]`);
                            if (!col) return;

                            // Visibility check
                            if (item.isVisible) {
                                col.style.display = 'flex';
                                visibleCount++;
                            } else {
                                col.style.display = 'none';
                            }

                            // Right column: Now Serving
                            const servingList = col.querySelector('.serving-list');
                            if (servingList) {
                                servingList.innerHTML = item.active.length > 0
                                    ? item.active.map(code => `<div class="ticket-box">${escapeHtml(code)}</div>`).join('')
                                    : `<div class="no-queue">No Queue</div>`;
                            }

                            // Left column: Waiting
                            const waitingList = col.querySelector('.waiting-list');
                            if (waitingList) {
                                waitingList.innerHTML = item.waiting.length > 0
                                    ? item.waiting.map(code => `<div class="ticket-box is-waiting">${escapeHtml(code)}</div>`).join('')
                                    : `<div class="no-queue">No Waiting</div>`;
                            }

                            // Update Avg Time
                            const avgVal = col.querySelector('.avg-time-val');
                            if (avgVal) {
                                avgVal.textContent = item.avgTime;
                            }
                        });

                        const noServicesMsg = document.getElementById('no-services-msg');
                        if (noServicesMsg) {
                            noServicesMsg.style.display = (visibleCount === 0) ? 'flex' : 'none';
                        }
                    }
                } catch (error) {
                    console.error('Error auto-refreshing queue cards:', error);
                }
            }

            function escapeHtml(str) {
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            // Automatically poll live data every 3 seconds
            setInterval(fetchLiveQueueData, 3000);
        });
    </script>
</body>
</html>