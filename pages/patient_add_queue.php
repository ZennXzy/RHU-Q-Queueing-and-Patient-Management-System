<?php
// Note: Session checking and role authorization can stay here or be handled in the external processor, 
// but keeping session checks here ensures security before rendering the view.
require_once '../api/check-private.php';
authorizeRoles(['Patient']);
require_once '../php/connection.php';

$currentPage = 'add_queue';
// Retrieve flash messages if passed from redirect
$message     = $_GET['msg'] ?? '';
$messageType = $_GET['type'] ?? '';

// Fetch services dynamically from the database
$servicesList = [];
$servicesQuery = $conn->query("SELECT service_name FROM services ORDER BY service_id ASC");
if ($servicesQuery) {
    while ($row = $servicesQuery->fetch_assoc()) {
        $servicesList[] = $row['service_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Queue - RHU-Q</title>
    <link rel="icon" href="../images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="../styles/toast.css">
    <link rel="stylesheet" href="../styles/patient_sidebar.css">
    <link rel="stylesheet" href="../styles/patient_add_queue.css">
    <link rel="stylesheet" href="../styles/logout_modal.css">

    <script src="../js/toast.js" defer></script>
    <script src="../js/logout.js" defer></script>
    <script src="../js/patient_sidebar.js" defer></script>
    <script src="../js/patient_add_queue.js" defer></script>
    <script src="../js/patient_notification.js" defer></script>

</head>
<body>
    <div class="toast-container" id="toastContainer"></div>

    <main class="main-content">
          <?php include '../pages/patient_queue_aside.php'; ?>
        <!-- Top App Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <!-- Include Patient Sidebar -->
                <?php include '../pages/patient_sidebar.php'; ?>
                <h2>Request Queue Ticket</h2>
            </div>
        </header>

        <div class="form-container">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <!-- Form targets the external PHP processor -->
            <form action="../php/patient_add_queue_process.php" method="POST" id="addQueueForm">
                
                <!-- Section 1: Service Selection -->
                <div class="form-card">
                    <h3 class="card-title">1. Select Health Service <span class="require_onced">*</span></h3>
                    <div class="form-group">
                        <select name="service" id="service" class="form-control" required>
                            <option value="" disabled selected>-- Choose Service --</option>
                            <?php foreach ($servicesList as $svc): ?>
                                <option value="<?php echo htmlspecialchars($svc); ?>"><?php echo htmlspecialchars($svc); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Section 2: Physical Examination & History -->
                <div class="form-card">
                    <h3 class="card-title">2. History & Physical Examination (PE) Findings</h3>
                    <p class="card-subtitle">Provide your recent vital signs if available (optional/initial intake):</p>
                    
                    <div class="grid-2-col">
                        <div class="form-group">
                            <label for="blood_pressure">Blood Pressure (mmHg)</label>
                            <input type="text" id="blood_pressure" name="blood_pressure" class="form-control" placeholder="e.g. 120/80">
                        </div>

                        <div class="form-group">
                            <label for="respiratory_rate">Respiratory Rate (bpm)</label>
                            <input type="number" step="1" id="respiratory_rate" name="respiratory_rate" class="form-control" placeholder="e.g. 18">
                        </div>

                        <div class="form-group">
                            <label for="temperature">Temperature (°C)</label>
                            <input type="number" step="0.1" id="temperature" name="temperature" class="form-control" placeholder="e.g. 36.6">
                        </div>

                        <div class="form-group">
                            <label for="pulse_rate">Pulse Rate (bpm)</label>
                            <input type="number" step="1" id="pulse_rate" name="pulse_rate" class="form-control" placeholder="e.g. 72">
                        </div>

                        <div class="form-group">
                            <label for="weight">Weight (kg)</label>
                            <input type="number" step="0.1" id="weight" name="weight" class="form-control" placeholder="e.g. 65.5">
                        </div>

                        <div class="form-group">
                            <label for="height">Height (cm)</label>
                            <input type="number" step="0.1" id="height" name="height" class="form-control" placeholder="e.g. 168">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Additional Description / Symptoms -->
                <div class="form-card">
                    <h3 class="card-title">3. Chief Complaint / Description</h3>
                    <div class="form-group">
                        <label for="description">Describe your symptoms or reason for visit:</label>
                        <textarea id="description" name="description" class="form-control" rows="4" placeholder="Briefly describe your current condition or specific request..."></textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">Reset Form</button>
                    <button type="submit" class="btn btn-primary">Submit Queue</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>