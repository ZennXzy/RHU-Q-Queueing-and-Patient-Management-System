<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';

try {
    // 1. Fetch total count of pending appointments
    $totalQuery = "SELECT COUNT(*) AS total FROM appointments WHERE status = 'Pending'";
    $totalRes =$conn->query($totalQuery);$totalRow = $totalRes ? $totalRes->fetch_assoc() : ['total' => 0];

    // 2. Query pending appointments along with patient details
    // Note: Adjust the JOIN table if patient full name resides in `patient_records` instead
    $query = "SELECT a.appointments_id, a.patient_id, a.service, a.remarks, a.appointment_date, a.created_at, 
                     TRIM(CONCAT_WS(' ', p.firstname, p.middlename, p.lastname, p.suffix)) AS patient_name,
                     p.contact_number
              FROM appointments a 
              LEFT JOIN patient_records p ON a.patient_id = p.patient_id 
              WHERE a.status = 'Pending' 
              ORDER BY a.appointment_date ASC";

    $res = $conn->query($query);

    ob_start();
    if ($res &&$res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $appointment_id = $row['appointments_id'];
            $patient_id     = $row['patient_id'];
            $patient_name   = !empty($row['patient_name']) ? htmlspecialchars($row['patient_name']) : 'Patient #' . $patient_id;
            $contact        = !empty($row['contact_number']) ? htmlspecialchars($row['contact_number']) : 'N/A';
            $service        = htmlspecialchars($row['service']);
            $remarks        = !empty($row['remarks']) ? htmlspecialchars($row['remarks']) : 'No description provided.';
            $formatted_date = date('M d, Y', strtotime($row['appointment_date']));
            ?>
            <div class="appointment-item" data-id="<?= $appointment_id ?>">
                <div class="patient-details">
                    <div class="patient-name"><?= htmlspecialchars($patient_name) ?></div>
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
                    <button class="btn-approve" data-id="<?= $appointment_id ?>" title="Approve">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Approve
                    </button>
                    <button class="btn-cancel" data-id="<?= $appointment_id ?>" title="Decline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="empty-state"><p>No pending appointment applications.</p></div>';
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