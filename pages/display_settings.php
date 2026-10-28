<?php
require_once '../api/check-private.php';
authorizeRoles(['Doctor/Staff']);
require_once '../php/connection.php';

// Fetch all services dynamically from the services table
$allServices = [];
$servicesQuery = $conn->query("SELECT service_name FROM services ORDER BY service_id ASC");
if ($servicesQuery) {
    while ($row = $servicesQuery->fetch_assoc()) {
        $allServices[] = $row['service_name'];
    }
}

// Fetch active services where status = 'Active' from the services table
$activeServices = [];
$activeQuery = $conn->query("SELECT service_name FROM services WHERE status = 'Active'");
if ($activeQuery) {
    while ($row = $activeQuery->fetch_assoc()) {
        $activeServices[] = $row['service_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHU-Q: Display Settings</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/display-settings.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/toast.css">
    <link rel="stylesheet" href="../styles/navbar.css">

    <script src="../js/submenu.js" defer></script>
    <script src="../js/dot-indicator.js" defer></script>
    <script src="../js/display-settings.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/session-check.js" defer></script>
    <script src="../js/toast.js" defer></script>
</head>
<body>
    <?php include '../pages/top-navbar.php'; ?>

    <div class="workspace">
        <div class="toast-container" id="toastContainer"></div>
        <?php include '../pages/sidebar.php'; ?>

        <div class="content-area">
            <div class="content-header">
                <h1 class="page-title">Display Settings</h1>
            </div>

            <!-- Two Column Layout Container -->
            <div class="settings-two-column-grid">

                <!-- Left Column: Queue Status Overview -->
                <div class="grid-card service-summary-card">
                    <h2 class="card-heading">Service Queue Overview</h2>
                    <p class="card-subtext">Real-time counts of patients currently queued across services.</p>
                    
                    <div class="service-stats-list">
                        <?php if (empty($allServices)): ?>
                            <div class="empty-state" style="text-align: center; padding: 20px; color: #6b7280;">No services found in the catalog.</div>
                        <?php else: ?>
                            <?php foreach ($allServices as $service): 
                                // Fetch In Progress count
                                $stmt1 = $conn->prepare("SELECT COUNT(*) AS total FROM queue_information WHERE service_name = ? AND status = 'In Progress'");
                                $stmt1->bind_param("s", $service);
                                $stmt1->execute();
                                $inProgressCount = $stmt1->get_result()->fetch_assoc()['total'] ?? 0;
                                $stmt1->close();

                                // Fetch Waiting count
                                $stmt2 = $conn->prepare("SELECT COUNT(*) AS total FROM queue_information WHERE service_name = ? AND status = 'Waiting'");
                                $stmt2->bind_param("s", $service);
                                $stmt2->execute();
                                $waitingCount = $stmt2->get_result()->fetch_assoc()['total'] ?? 0;
                                $stmt2->close();
                            ?>
                                <div class="service-stat-item">
                                    <div class="service-name-label"><?php echo htmlspecialchars($service); ?></div>
                                    <div class="stat-pills">
                                        <span class="badge badge-in-progress" title="In Progress"><?php echo $inProgressCount; ?> Serving
                                        </span>
                                        <span class="badge badge-waiting" title="Waiting"><?php echo $waitingCount; ?> Waiting
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column: Service Checklist Grid & Save Button -->
                <div class="grid-card service-checklist-card">
                    <form id="display-settings-form">
                        <div class="checklist-header">
                            <div>
                                <h2 class="card-heading">Active Display Cards</h2>
                                <p class="card-subtext">Check the services you want visible on the Queue Display screen.</p>
                            </div>
                        </div>

                        <div class="checklist-grid">
                            <?php if (empty($allServices)): ?>
                                <p style="color: #6b7280;">No services available to display.</p>
                            <?php else: ?>
                                <?php foreach ($allServices as $index => $service): 
                                    $isChecked = in_array($service, $activeServices);
                                ?>
                                    <label class="checklist-cell <?php echo $isChecked ? 'active' : ''; ?>">
                                        <input type="checkbox" name="services[]" value="<?php echo htmlspecialchars($service); ?>" <?php echo $isChecked ? 'checked' : ''; ?>>
                                        <span class="checkbox-custom"></span>
                                        <span class="service-title"><?php echo htmlspecialchars($service); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="checklist-actions">
                            <button type="submit" id="save-settings-btn" class="save-settings-btn">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</body>
</html>