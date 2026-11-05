
<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$queue_info_id = (int)($_POST['queue_info_id'] ?? 0);
$patient_name = trim($_POST['patient_name'] ?? '');
$queue_code = trim($_POST['queue_code'] ?? '');
$service = trim($_POST['service'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');
$appointment = (int)($_POST['add_appointment'] ?? 0);
$appointment_date = trim($_POST['appointment_date'] ?? '');
$server_name = $_SESSION['logged_id'] ?? '';

if ($queue_info_id <= 0 || $patient_name === '' || $service === '') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters provided.']);
    exit;
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("SELECT patient_id, queue_code, service_snapshot, accepted_at, status FROM queue_information WHERE queue_info_id = ? FOR UPDATE");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("i", $queue_info_id);
    $stmt->execute();
    $queue = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$queue) throw new Exception('Queue record not found.');
    if ($queue['status'] === 'Completed') throw new Exception('This queue has already been completed.');
    if ($queue['status'] !== 'In Progress') throw new Exception('Only queues in progress can be completed.');

    $patient_id = (int)$queue['patient_id'];
    $queue_code = $queue['queue_code'];
    $queue_service = trim((string)$queue['service_snapshot']);

    $stmt = $conn->prepare("SELECT contact_number FROM patient_records WHERE patient_id = ? LIMIT 1");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $patientRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $patient_contact = $patientRow['contact_number'] ?? null;

    $stmt = $conn->prepare("SELECT service_id FROM services WHERE service_name = ? LIMIT 1");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("s", $service);
    $stmt->execute();
    $serviceRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$serviceRow) throw new Exception('Please select a valid service.');
    $service_id = (int)$serviceRow['service_id'];

    $stmt = $conn->prepare("UPDATE queue_information SET status = 'Completed', service_duration = NOW() WHERE queue_info_id = ? AND status = 'In Progress'");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("i", $queue_info_id);

    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        throw new Exception('Failed to complete queue.');
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT accepted_at, service_duration FROM queue_information WHERE queue_info_id = ?");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("i", $queue_info_id);
    $stmt->execute();
    $timeRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $start = new DateTime($timeRow['accepted_at'] ?: 'now');
    $end = new DateTime($timeRow['service_duration'] ?: 'now');
    $totalSeconds = max(0, $end->getTimestamp() - $start->getTimestamp());
    $interval = $start->diff($end);
    $service_duration = $interval->format('%a days, %h hours, %i minutes');

    $insertSql = "INSERT INTO transactions
        (patient_id, queue_code, service, service_snapshot, remarks, server_name, service_duration, transaction_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($insertSql);
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("isissis", $patient_id, $queue_code, $service_id, $service, $remarks, $server_name, $service_duration);

    if (!$stmt->execute()) throw new Exception('Failed to save completed transaction.');
    $stmt->close();

    $completedAppointmentId = null;

    if ($queue_service !== '') {
        $stmt = $conn->prepare("SELECT appointments_id FROM appointments WHERE patient_id = ? AND service = ? AND status = 'Queued' FOR UPDATE");
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param("is", $patient_id, $queue_service);
        $stmt->execute();
        $matches = $stmt->get_result();

        if ($matches->num_rows > 1) {
            $stmt->close();
            throw new Exception('Multiple queued appointments match this patient and service. Please verify the records.');
        }

        if ($matches->num_rows === 1) {
            $completedAppointmentId = (int)$matches->fetch_assoc()['appointments_id'];
        }
        $stmt->close();

        if ($completedAppointmentId !== null) {
            $stmt = $conn->prepare("UPDATE appointments SET status = 'Completed' WHERE appointments_id = ? AND status = 'Queued'");
            if (!$stmt) throw new Exception($conn->error);
            $stmt->bind_param("i", $completedAppointmentId);

            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                throw new Exception('Failed to complete linked appointment.');
            }
            $stmt->close();
        }
    }

    if ($appointment === 1 && $appointment_date !== '') {
        $appointment_status = 'Approved';

        $appointmentSql = "INSERT INTO appointments
            (patient_id, contact_number, service, remarks, server_name, status, appointment_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($appointmentSql);
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param(
            "isssiss",
            $patient_id, $patient_contact, $service, $remarks,
            $server_name, $appointment_status, $appointment_date
        );

        if (!$stmt->execute()) throw new Exception('Failed to create follow-up appointment.');
        $stmt->close();

        $logMessage = ($_SESSION['logged_name'] ?? 'Staff') . ' added an Appointment.';
        $stmt = $conn->prepare("INSERT INTO logs (log_message) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param("s", $logMessage);
            $stmt->execute();
            $stmt->close();
        }
    }

    $logMessage = ($_SESSION['logged_account'] ?? 'Staff') . ' completed a task.';
    $stmt = $conn->prepare("INSERT INTO logs (log_message) VALUES (?)");
    if ($stmt) {
        $stmt->bind_param("s", $logMessage);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction completed successfully.',
        'duration' => $totalSeconds,
        'completed_appointment_id' => $completedAppointmentId
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    error_log('Complete queue error: ' . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
