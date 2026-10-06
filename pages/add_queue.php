<?php
require_once '../api/check-private.php';
require_once '../php/connection.php';

// Enforce Patient Authorization
authorizeRoles(['Patient']);

$currentPage = 'add_queue';
$patient_id  = $_SESSION['patient_id'] ?? null;
$message     = '';
$messageType = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service          = trim($_POST['service'] ?? '');
    $bp               = trim($_POST['blood_pressure'] ?? '');
    $respiratory_rate = trim($_POST['respiratory_rate'] ?? '');
    $temperature      = trim($_POST['temperature'] ?? '');
    $pulse_rate       = trim($_POST['pulse_rate'] ?? '');
    $weight           = trim($_POST['weight'] ?? '');
    $height           = trim($_POST['height'] ?? '');
    $description      = trim($_POST['description'] ?? '');

    if (empty($service)) {
        $message = "Please select a health service.";
        $messageType = "error";
    } else {
        // Insert Queue Record
        $stmt = $conn->prepare("INSERT INTO queue_information 
            (patient_id, service_type, blood_pressure, respiratory_rate, temperature, pulse_rate, weight, height, description, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Waiting', NOW())");

        if ($stmt) {
            $stmt->bind_param("issssssss", $patient_id, $service, $bp, $respiratory_rate, $temperature, $pulse_rate, $weight, $height, $description);
            
            if ($stmt->execute()) {
                $queue_id = $conn->insert_id;
                
                // Log activity
                $logStmt = $conn->prepare("INSERT INTO logs (log_message) VALUES (?)");
                if ($logStmt) {
                    $logMsg = "Patient (ID: $patient_id) submitted a new queue ticket #$queue_id for $service.";
                    $logStmt->bind_param("s", $logMsg);
                    $logStmt->execute();
                    $logStmt->close();
                }

                header("Location: queue_display_page.php?msg=" . urlencode("Queue request submitted successfully!") . "&type=success");
                exit();
            } else {
                $message = "Failed to submit queue. Please try again.";
                $messageType = "error";
            }
            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
            $messageType = "error";
        }
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

    <script src="../js/toast.js" defer></script>
    <script src="../js/patient_sidebar.js" defer></script>
</head>
<body>
    <div class="toast-container" id="toastContainer"></div>

    <main class="main-content">
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
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form action="../php/patient_add_queue.php" method="POST" id="addQueueForm">
                
                <!-- Section 1: Service Selection -->
                <div class="form-card">
                    <h3 class="card-title">1. Select Health Service <span class="required">*</span></h3>
                    <div class="form-group">
                        <select name="service" id="service" class="form-control" required>
                            <option value="" disabled selected>-- Choose Service --</option>
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