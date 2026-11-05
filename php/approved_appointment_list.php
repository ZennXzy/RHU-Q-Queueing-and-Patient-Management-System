
<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../php/connection.php';
require_once '../api/check-private.php';

date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');

try {
    $countQuery = "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved_count,
        SUM(CASE WHEN status = 'Queued' THEN 1 ELSE 0 END) AS queued_count,
        SUM(CASE WHEN status = 'Approved' AND appointment_date < DATE_ADD(?, INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS due_count
        FROM appointments
        WHERE status IN ('Approved', 'Queued')";

    $countStmt = $conn->prepare($countQuery);
    if (!$countStmt) throw new Exception($conn->error);

    $countStmt->bind_param("s", $today);
    if (!$countStmt->execute()) throw new Exception($countStmt->error);

    $counts = $countStmt->get_result()->fetch_assoc();
    $countStmt->close();

    $total = (int)($counts['total'] ?? 0);
    $approvedCount = (int)($counts['approved_count'] ?? 0);
    $queuedCount = (int)($counts['queued_count'] ?? 0);
    $dueCount = (int)($counts['due_count'] ?? 0);

    if (($_GET['mode'] ?? 'list') === 'count') {
        echo json_encode([
            'status' => 'success',
            'total' => $total,
            'approved_count' => $approvedCount,
            'queued_count' => $queuedCount,
            'due_count' => $dueCount,
            'today' => $today
        ]);
        exit;
    }

    $query = "SELECT a.appointments_id, a.patient_id, a.service, a.remarks,
                     a.appointment_date, a.created_at, a.status,
                     TRIM(CONCAT_WS(' ', p.firstname, p.middlename, p.lastname, p.suffix)) AS patient_name,
                     p.contact_number
              FROM appointments a
              LEFT JOIN patient_records p ON a.patient_id = p.patient_id
              WHERE a.status IN ('Approved', 'Queued')
              ORDER BY CASE WHEN a.status = 'Approved' THEN 0 ELSE 1 END,
                       a.appointment_date ASC";

    $res = $conn->query($query);
    if (!$res) throw new Exception($conn->error);

    $escape = static function ($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    };

    ob_start();

    if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $appointmentId = (int)$row['appointments_id'];
            $patientId = (int)$row['patient_id'];
            $patientName = trim((string)($row['patient_name'] ?? '')) ?: "Patient #$patientId";
            $contact = $row['contact_number'] ?: 'N/A';
            $service = (string)($row['service'] ?? '');
            $remarks = $row['remarks'] ?: 'No description provided.';
            $status = (string)$row['status'];

            $appointmentDate = substr((string)$row['appointment_date'], 0, 10);
            $timestamp = strtotime($appointmentDate);
            $formattedDate = $timestamp !== false ? date('M d, Y', $timestamp) : 'Invalid date';

            $isQueued = $status === 'Queued';
            $isDue = !$isQueued && $timestamp !== false && $appointmentDate <= $today;
            $dueClass = $isDue ? ' appointment-due' : '';
            $dueLabel = $appointmentDate < $today ? 'Overdue' : 'Due Today';
            ?>
            <div class="appointment-item<?= $dueClass ?><?= $isQueued ? ' appointment-queued' : '' ?>" data-id="<?= $appointmentId ?>" data-patient-id="<?= $patientId ?>" data-appointment-date="<?= $escape($appointmentDate) ?>">
                <div class="patient-details">
                    <div class="patient-name"><?= $escape($patientName) ?></div>
                    <div class="info-row"><strong>Contact:</strong> <?= $escape($contact) ?></div>
                    <div class="appointment-meta">
                        <span class="service-tag"><?= $escape($service) ?></span>
                        <span class="date-tag">
                            <img src="../svg/reminder-appointment.svg" alt="calendar-icon">
                            <?= $formattedDate ?>
                        </span>
                        <?php if ($isDue): ?>
                            <span class="appointment-due-badge"><?= $dueLabel ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="appointment-remarks">"<?= $escape($remarks) ?>"</p>
                </div>
                <div class="action-buttons">
                    <?php if ($isQueued): ?>
                        <span class="appointment-queued-label" title="Patient is already in queue">Queued</span>
                    <?php else: ?>
                        <button type="button" class="btn-complete" data-id="<?= $appointmentId ?>" data-patient-id="<?= $patientId ?>" data-patient-name="<?= $escape($patientName) ?>" data-service="<?= $escape($service) ?>" title="Add Patient to Queue">
                            <img style="height:16px; filter:brightness(0) invert(1);" src="../svg/add-queue.svg" alt="add-queue-icon">
                            Add to Queue
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="empty-state"><p>No approved or queued appointments found.</p></div>';
    }

    $html = ob_get_clean();

    echo json_encode([
        'status' => 'success',
        'html' => $html,
        'total' => $total,
        'approved_count' => $approvedCount,
        'queued_count' => $queuedCount,
        'due_count' => $dueCount,
        'today' => $today
    ]);
} catch (Throwable $e) {
    if (ob_get_level() > 0) ob_end_clean();
    error_log('Approved appointment list error: ' . $e->getMessage());
    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to load appointments.'
    ]);
} finally {
    $conn->close();
}
?>
