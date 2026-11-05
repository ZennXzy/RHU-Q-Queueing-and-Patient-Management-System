
<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';
authorizeRoles(['Patient']);
date_default_timezone_set('Asia/Manila');

$patient_id = (int)($_SESSION['patient_id'] ?? 0);
$today = date('Y-m-d');

try {
    if ($patient_id <= 0) throw new Exception('Unauthorized session.');

    $stmt = $conn->prepare("SELECT appointments_id, service, remarks, appointment_date, status, note, created_at FROM appointments WHERE patient_id = ? ORDER BY appointment_date DESC");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $res = $stmt->get_result();

    ob_start();
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $appointment_id = (int)$row['appointments_id'];
            $service = htmlspecialchars((string)$row['service'], ENT_QUOTES, 'UTF-8');
            $remarks = !empty($row['remarks']) ? htmlspecialchars((string)$row['remarks'], ENT_QUOTES, 'UTF-8') : 'No description provided.';
            $status = (string)$row['status'];
            $safe_status = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
            $status_class = strtolower($status);
            $decline_reason = !empty($row['note']) ? htmlspecialchars((string)$row['note'], ENT_QUOTES, 'UTF-8') : 'No reason provided.';
            $appointment_day = date('Y-m-d', strtotime($row['appointment_date']));
            $formatted_date = date('M d, Y', strtotime($row['appointment_date']));
            $is_approved = $status === 'Approved';
            $can_queue = $is_approved && $appointment_day <= $today;
            ?>
            <div class="appointment-item <?= htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8') ?>" data-id="<?= $appointment_id ?>">
                <div class="patient-details">
                    <div class="patient-header" style="display: flex; align-items: center; justify-content: space-between;">
                        <div class="patient-name"><?= $service ?></div>
                        <span class="status-tag status-<?= htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8') ?>"><?= $safe_status ?></span>
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

                    <div class="patient-appointment-actions">
                        <?php if ($is_approved): ?>
                            <button
                                type="button"
                                class="btn-patient-queue"
                                data-appointment-id="<?= $appointment_id ?>"
                                <?= $can_queue ? '' : 'disabled' ?>
                            >
                                <?= $can_queue ? 'Add to Queue' : 'Available on ' . $formatted_date ?>
                            </button>
                        <?php endif; ?>

                        <button
                            type="button"
                            class="btn-patient-remove"
                            data-appointment-id="<?= $appointment_id ?>"
                        >
                            Remove
                        </button>
                    </div>
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
        'html' => $html,
        'total' => $res ? $res->num_rows : 0
    ]);

} catch (Throwable $e) {
    if (ob_get_level() > 0) ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to load appointments.'
    ]);
}

$conn->close();
?>
