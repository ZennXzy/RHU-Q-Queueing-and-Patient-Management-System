<?php
require '../api/check-private.php';
authorizeRoles(['Doctor/Staff', 'Display']);
require '../php/connection.php';

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
</head>
<body>
    <div class="page-container">
        <div class="top-brand">
            <div class="rhu_logo_topNav">
                <img src="../images/rhu_logo_topNav.png" alt="rhu_logo">
            </div> 
            RHU-Q: Queuing and Patient Management System
        </div>

        <h1 class="page-title">Queue Display</h1>
        <div class="display-card grid-dynamic">
            <div id="no-services-msg" class="no-services-message" style="display: <?php echo empty($activeServices) ? 'block' : 'none'; ?>;">
                <h3>No Selected Services</h3>
                <p>Please select at least one service card from the display settings.</p>
            </div>
            
            <?php foreach ($DEPARTMENT_CONFIG as $serviceName => $config): 
                $isVisible = in_array($serviceName, $activeServices);
                
                $sqlActive = "SELECT queue_code FROM queue_information WHERE service_name = ? AND status = 'In Progress' ORDER BY created_at ASC";
                $stmtActive = $conn->prepare($sqlActive);
                $stmtActive->bind_param("s", $serviceName);
                $stmtActive->execute();
                $resActive = $stmtActive->get_result();

                $sqlWaiting = "SELECT queue_code FROM queue_information WHERE service_name = ? AND status = 'Waiting' ORDER BY created_at ASC";
                $stmtWaiting = $conn->prepare($sqlWaiting);
                $stmtWaiting->bind_param("s", $serviceName);
                $stmtWaiting->execute();
                $resWaiting = $stmtWaiting->get_result();

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

                        <?php if ($resActive && $resActive->num_rows > 0): ?>
                            <?php while ($activeRow = $resActive->fetch_assoc()): ?>
                                <div class="ticket-box"><?php echo htmlspecialchars($activeRow['queue_code']); ?></div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-queue">No Queue</div>
                        <?php endif; ?>

                        <div class="time-stat">
                            <div class="label">Average Waiting Time</div>
                            <div class="value"><?php echo $avgFormatted; ?></div>
                        </div>
                    </div>

                    <div class="waiting-card">
                        <div class="waiting-column">
                            <div class="waiting-section">
                                <div class="waiting-title">Waiting</div>
                                <div class="waiting-badges">
                                    <?php if ($resWaiting && $resWaiting->num_rows > 0): ?>
                                        <?php while ($waitingRow = $resWaiting->fetch_assoc()): ?>
                                            <span class="badge"><?php echo htmlspecialchars($waitingRow['queue_code']); ?></span>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <span>- No waiting items -</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>

            <div class="minimize-btn" title="Toggle Fullscreen">
                <a href="../pages/queue_manager_page.php" class="minimize-button" title="Exit Display">
                    <img src="../svg/minimize-icon.svg" alt="minimize-button">
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            async function syncDisplayCardsWithDB() {
                try {
                    const response = await fetch('../api/get_queue_card_settings.php');
                    const result = await response.json();

                    if (result.status === 'success' && Array.isArray(result.data)) {
                        const activeList = result.data
                            .filter(item => item.status === 'Active' || item.status === '1')
                            .map(item => item.service_type);

                        let visibleCount = 0;
                        document.querySelectorAll('.dept-column').forEach(col => {
                            const service = col.getAttribute('data-service');
                            if (activeList.includes(service)) {
                                col.style.display = 'flex';
                                visibleCount++;
                            } else {
                                col.style.display = 'none';
                            }
                        });

                        const noServicesMsg = document.getElementById('no-services-msg');
                        if (noServicesMsg) {
                            noServicesMsg.style.display = (visibleCount === 0) ? 'block' : 'none';
                        }
                    }
                } catch (error) {
                    console.error('Error syncing queue cards:', error);
                }
            }

            // Sync display cards every 5 seconds across all monitors
            setInterval(syncDisplayCardsWithDB, 5000);
        });
    </script>
</body>
</html>