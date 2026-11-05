
<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../php/connection.php';
require_once '../api/check-private.php';
authorizeRoles(['Patient']);
date_default_timezone_set('Asia/Manila');

$patient_id = (int)($_SESSION['patient_id'] ?? 0);
$appointment_id = (int)($_POST['appointment_id'] ?? 0);
$transactionStarted = false;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }
    if ($patient_id <= 0 || $appointment_id <= 0) {
        throw new Exception('Invalid patient session or appointment.');
    }

    $conn->begin_transaction();
    $transactionStarted = true;

    $stmt = $conn->prepare("SELECT appointments_id, service, appointment_date, status FROM appointments WHERE appointments_id = ? AND patient_id = ? FOR UPDATE");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param('ii', $appointment_id, $patient_id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$appointment) throw new Exception('Appointment not found.');
    if ($appointment['status'] !== 'Approved') {
        throw new Exception('Only approved appointments can be added to queue.');
    }
    if (date('Y-m-d', strtotime($appointment['appointment_date'])) > date('Y-m-d')) {
        throw new Exception('Your scheduled appointment date has not arrived yet.');
    }

    $service_name = $appointment['service'];

    $stmt = $conn->prepare("SELECT service_id FROM services WHERE service_name = ? LIMIT 1");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param('s', $service_name);
    $stmt->execute();
    $serviceRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$serviceRow) throw new Exception('Appointment service is unavailable.');
    $service_id = (int)$serviceRow['service_id'];

    $stmt = $conn->prepare("SELECT queue_code, status FROM queue_information WHERE patient_id = ? AND DATE(created_at) = CURDATE() AND status IN ('Waiting', 'In Progress') LIMIT 1");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param('i', $patient_id);
    $stmt->execute();
    $duplicate = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($duplicate) {
        throw new Exception('You already have an active queue ticket: ' . $duplicate['queue_code']);
    }

    $services = $conn->query("SELECT service_id FROM services ORDER BY service_id ASC");
    if (!$services) throw new Exception($conn->error);

    $prefix = 'Z';
    $index = 0;
    while ($row = $services->fetch_assoc()) {
        if ((int)$row['service_id'] === $service_id) {
            if ($index < 26) $prefix = chr(65 + $index);
            break;
        }
        $index++;
    }

    $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(queue_code, 2) AS UNSIGNED)) AS max_num FROM queue_information WHERE queue_code LIKE CONCAT(?, '%') AND DATE(created_at) = CURDATE()");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param('s', $prefix);
    $stmt->execute();
    $codeRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $nextNumber = (int)($codeRow['max_num'] ?? 0) + 1;
    $queue_code = sprintf('%s%03d', $prefix, $nextNumber);
    $initial_status = 'Waiting';

    $stmt = $conn->prepare("INSERT INTO queue_information (queue_code, patient_id, service_name, service_snapshot, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param('siiss', $queue_code, $patient_id, $service_id, $service_name, $initial_status);

    if (!$stmt->execute()) throw new Exception('Failed to create queue ticket.');
    $queue_info_id = (int)$conn->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("UPDATE appointments SET status = 'Queued' WHERE appointments_id = ? AND patient_id = ? AND status = 'Approved'");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param('ii', $appointment_id, $patient_id);

    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        throw new Exception('Failed to update appointment status.');
    }
    $stmt->close();

    $conn->commit();
    $transactionStarted = false;

    echo json_encode([
        'status' => 'success',
        'message' => 'Successfully registered in queue.',
        'queue_info_id' => $queue_info_id,
        'queue_code' => $queue_code,
        'appointment_id' => $appointment_id
    ]);
} catch (Throwable $e) {
    if ($transactionStarted) $conn->rollback();
    error_log('Patient queue registration: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
$conn->close();
?>
