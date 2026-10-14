<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';
date_default_timezone_set('Asia/Manila');

$patient_id = $_SESSION['patient_id'] ?? 0;

try {
    if ($patient_id <= 0) {
        throw new Exception("Unauthorized session.");
    }

    $stmt = $conn->prepare("SELECT appointments_id, service, remarks, appointment_date, status, note, created_at 
                            FROM appointments 
                            WHERE patient_id = ? 
                            ORDER BY appointment_date DESC");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $res = $stmt->get_result();

    ob_start();
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $appointment_id = $row['appointments_id'];
            $service        = htmlspecialchars($row['service']);
            $remarks        = !empty($row['remarks']) ? htmlspecialchars($row['remarks']) : 'No description provided.';
            $status         = htmlspecialchars($row['status']);
            $decline_reason = !empty($row['note']) ? htmlspecialchars($row['note']) : 'No reason provided.';
            $formatted_date = date('M d, Y', strtotime($row['appointment_date']));
            ?>
            <div class="appointment-item <?= strtolower($status) ?>" data-id="<?= $appointment_id ?>">
                <div class="patient-details">
                    <div class="patient-header" style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="patient-name"><?= $service ?></div>
                        <span  class="status-tag status-<?= strtolower($status) ?>"><?= $status ?></span>
                    </div>

                    <div class="appointment-meta" style="margin-top: 6px;">
                        <span class="date-tag">
                            <img src="../svg/reminder-appointment.svg" alt="calendar-icon">
                            Scheduled: <?= $formatted_date ?>
                        </span>
                    </div>

                    <p class="appointment-remarks">"<?= $remarks ?>"</p>

                    <?php if ($status === 'Declined'): ?>
                        <p class="decline-reason-text" style="color: #d9534f; font-size: 13px; margin-top: 6px; border-top: 1px solid #e2e8f0; padding-top: 4px;">
                            <strong>Reason:</strong> "<?= $decline_reason ?>"
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="empty-state"><p>You have no scheduled or past appointments.</p></div>';
    }
    $html = ob_get_clean();

    echo json_encode([
        'status' => 'success',
        'html'   => $html,
        'total'  => $res ? $res->num_rows : 0
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