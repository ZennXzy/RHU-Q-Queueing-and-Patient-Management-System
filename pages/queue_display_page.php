<?php
require '../php/check-private.php';
require '../php/connection.php';

$DEPARTMENT_CONFIG = [
    'Medical Consultation' => [
        'title'      => 'MEDICAL CONSULTATION',
        'themeClass' => 'dept-medical',
        'icon'       => '../svg/medical-icon.svg',
    ],
    'Prenatal Checkup' => [
        'title'      => 'PRENATAL CHECKUP',
        'themeClass' => 'dept-medical',
        'icon'       => '../svg/prenatal-icon.svg',
    ],
    'Immunization / Vaccination' => [
        'title'      => 'VACCINATION',
        'themeClass' => 'dept-vaccine',
        'icon'       => '../svg/vaccine-icon.svg',
    ],
    'Dental Checkup' => [
        'title'      => 'DENTAL CHECKUP',
        'themeClass' => 'dept-dental',
        'icon'       => '../svg/dental-icon.svg',
    ],
    'Laboratory Request' => [
        'title'      => 'LABORATORY',
        'themeClass' => 'dept-medical',
        'icon'       => '../svg/laboratory-icon.svg',
    ]
];

// Fallback list of services
$defaultServices = [
    'Medical Consultation',
    'Prenatal Checkup',
    'Immunization / Vaccination',
    'Dental Checkup',
    'Laboratory Request'
];
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

        <!-- Class grid-dynamic enables automatic 2-row stacking when cards exceed 4-5 items -->
        <div class="display-card grid-dynamic">
            
            <?php foreach ($defaultServices as $serviceName): 
                $config = $DEPARTMENT_CONFIG[$serviceName] ?? [
                    'title'      => strtoupper($serviceName),
                    'themeClass' => 'dept-medical',
                    'icon'       => '../svg/medical-icon.svg',
                ];

                // $sqlActive = "SELECT queue_code FROM queue_information 
                //                 WHERE service_name = ? AND status = 'In Progress' 
                //                 ORDER BY created_at ASC LIMIT 1";

                // 1. FETCH IN-PROGRESS TICKETS
                $sqlActive = "SELECT queue_code FROM queue_information 
                              WHERE service_name = ? AND status = 'In Progress' 
                              ORDER BY created_at ASC";
                $stmtActive = $conn->prepare($sqlActive);
                $stmtActive->bind_param("s", $serviceName);
                $stmtActive->execute();
                $resActive = $stmtActive->get_result();

                // 2. FETCH WAITING TICKETS
                $sqlWaiting = "SELECT queue_code FROM queue_information 
                               WHERE service_name = ? AND status = 'Waiting' 
                               ORDER BY created_at ASC";
                $stmtWaiting = $conn->prepare($sqlWaiting);
                $stmtWaiting->bind_param("s", $serviceName);
                $stmtWaiting->execute();
                $resWaiting = $stmtWaiting->get_result();

                // 3. FETCH AVERAGE WAITING TIME
                $sqlAvg = "SELECT AVG(service_duration) as avg_sec 
                           FROM transactions 
                           WHERE service = ? AND DATE(transaction_date) = CURDATE()";
                $stmtAvg = $conn->prepare($sqlAvg);
                $stmtAvg->bind_param("s", $serviceName);
                $stmtAvg->execute();
                $resAvg = $stmtAvg->get_result();
                
                $avgFormatted = '00:00:00';
                if ($rowAvg = $resAvg->fetch_assoc()) {
                    if (!empty($rowAvg['avg_sec'])) {
                        $secs = (int)round($rowAvg['avg_sec']);
                        $h = floor($secs / 3600);
                        $m = floor(($secs % 3600) / 60);
                        $s = $secs % 60;
                        $avgFormatted = sprintf('%02d:%02d:%02d', $h, $m, $s);
                    }
                }
            ?>

                <div class="dept-column" data-service="<?php echo htmlspecialchars($serviceName); ?>">
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

    <!-- Filter cards based on settings in localStorage -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const stored = localStorage.getItem('activeDepartments');
            if (stored) {
                const activeDepts = JSON.parse(stored).map(d => d.id || d.name);
                document.querySelectorAll('.dept-column').forEach(col => {
                    const service = col.getAttribute('data-service');
                    if (!activeDepts.includes(service)) {
                        col.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>