<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';
date_default_timezone_set('Asia/Manila');

try {
    // 1. Fetch total count of approved appointments
    $totalQuery = "SELECT COUNT(*) AS total FROM appointments WHERE status = 'Approved'";
    $totalRes = $conn->query($totalQuery);
    $totalRow = $totalRes ? $totalRes->fetch_assoc() : ['total' => 0];

    // 2. Query approved appointments joined with patient_records
    $query = "SELECT a.appointments_id, a.patient_id, a.service, a.remarks, a.appointment_date, a.created_at, 
                     TRIM(CONCAT_WS(' ', p.firstname, p.middlename, p.lastname, p.suffix)) AS patient_name,
                     p.contact_number
              FROM appointments a 
              LEFT JOIN patient_records p ON a.patient_id = p.patient_id 
              WHERE a.status = 'Approved' 
              ORDER BY a.appointment_date ASC";

    $res = $conn->query($query);

    ob_start();
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $appointment_id = $row['appointments_id'];
            $patient_id     = $row['patient_id'];
            $patient_name   = !empty($row['patient_name']) ? htmlspecialchars((string)$row['patient_name']) : 'Patient #' . $patient_id;
            $contact        = !empty($row['contact_number']) ? htmlspecialchars((string)$row['contact_number']) : 'N/A';
            $service        = htmlspecialchars((string)$row['service']);
            $remarks        = !empty($row['remarks']) ? htmlspecialchars((string)$row['remarks']) : 'No description provided.';
            $formatted_date = date('M d, Y', strtotime($row['appointment_date']));
            ?>
            <div class="appointment-item" data-id="<?= $appointment_id ?>" data-patient-id="<?= $patient_id ?>">
                <div class="patient-details">
                    <div class="patient-name"><?= $patient_name ?></div>
                    <div class="info-row">
                        <strong>Contact:</strong> <?= $contact ?>
                    </div>
                    <div class="appointment-meta">
                        <span class="service-tag"><?= $service ?></span>
                        <span class="date-tag">
                            <img src="../svg/reminder-appointment.svg" alt="calendar-icon">
                            <?= $formatted_date ?>
                        </span>
                    </div>
                    <p class="appointment-remarks">"<?= $remarks ?>"</p>
                </div>

                <div class="action-buttons">
                    <button class="btn-complete" data-id="<?= $appointment_id ?>" data-patient-id="<?= $patient_id ?>" data-patient-name="<?= $patient_name ?>" data-service="<?= $service ?>" title="Complete Service">
                        <img style="height:16px; filter: brightness(0) invert(1);" src="../svg/add-queue.svg" alt="add-queue-icon">
                        Add to Queue
                    </button>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="empty-state"><p>No approved appointments found.</p></div>';
    }
    $html = ob_get_clean();

    echo json_encode([
        'status' => 'success',
        'html'   => $html,
        'total'  => (int)($totalRow['total'] ?? 0)
    ]);

} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>