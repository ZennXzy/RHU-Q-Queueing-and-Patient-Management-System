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

    // Fetch Queue Record
    $stmt = $conn->prepare("SELECT * FROM queue_information WHERE queue_info_id = ? FOR UPDATE");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("i", $queue_info_id);
    $stmt->execute();
    $queue = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$queue) {
        throw new Exception('Queue record not found.');
    }
    if ($queue['status'] === 'Completed') {
        throw new Exception('This queue has already been completed.');
    }
    if ($queue['status'] !== 'In Progress') {
        throw new Exception("Cannot complete queue. Current status is '{$queue['status']}' instead of 'In Progress'.");
    }

    $patient_id = (int)$queue['patient_id'];
    $queue_code = $queue['queue_code'];
    $queue_service = trim((string)$queue['service_snapshot']);
    $accepted_at = $queue['accepted_at'];

    // Calculate Service Duration
    if (empty($accepted_at) || $accepted_at === '0000-00-00 00:00:00') {
        throw new Exception('Service start time is missing. Cannot calculate service duration.');
    }

    $start = new DateTime($accepted_at);
    $end = new DateTime('now');
    $totalSeconds = $end->getTimestamp() - $start->getTimestamp();

    if ($totalSeconds < 0) {
        throw new Exception('Service start time cannot be later than completion time.');
    }
    if ($totalSeconds > 2147483647) {
        throw new Exception('Service duration exceeds the database limit.');
    }

    $service_duration = (int)$totalSeconds;

    // Update Queue Status
    $stmt = $conn->prepare("UPDATE queue_information SET status = 'Completed', service_duration = ? WHERE queue_info_id = ?");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("ii", $service_duration, $queue_info_id);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        throw new Exception('Failed to update queue completion status.');
    }
    $stmt->close();

    // Get Patient Contact
    $stmt = $conn->prepare("SELECT contact_number FROM patient_records WHERE patient_id = ? LIMIT 1");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $patientRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $patient_contact = $patientRow['contact_number'] ?? null;

    // Get Service ID
    $stmt = $conn->prepare("SELECT service_id FROM services WHERE service_name = ? LIMIT 1");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("s", $service);
    $stmt->execute();
    $serviceRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$serviceRow) {
        throw new Exception('Please select a valid service.');
    }

    $service_id = (int)$serviceRow['service_id'];
    $transaction_date = date('Y-m-d H:i:s');
    // Save Completed Transaction
    $insertSql = "
        INSERT INTO transactions (
            patient_id, queue_code, service_id, service_snapshot,
            blood_pressure, respiratory_rate, temperature, pulse_rate,
            weight, height, remarks, chief_complaint, staff_id,
            service_duration, transaction_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($insertSql);
    if (!$stmt) throw new Exception($conn->error);

    $staff_id = (int)$server_name;
    $stmt->bind_param(
        "isissididdssiis",
        $patient_id,
        $queue_code,
        $service_id,
        $service,
        $queue['blood_pressure'],
        $queue['respiratory_rate'],
        $queue['temperature'],
        $queue['pulse_rate'],
        $queue['weight'],
        $queue['height'],
        $remarks,
        $queue['chief_complaint'],
        $staff_id,
        $service_duration,
        $transaction_date
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to save completed transaction.');
    }
    $stmt->close();

    // Complete Linked Appointment
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

    // Create Follow-up Appointment
    $transaction_date = date('Y-m-d H:i:s');
    if ($appointment === 1 && $appointment_date !== '') {
        $appointment_status = 'Approved';
        $appointmentSql = "
            INSERT INTO appointments (
                patient_id, service, remarks, staff_id, status, appointment_date, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($appointmentSql);
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param("ississs", $patient_id, $service, $remarks, $staff_id, $appointment_status, $appointment_date, $transaction_date);
        if (!$stmt->execute()) {
            throw new Exception('Failed to create follow-up appointment.');
        }
        $stmt->close();

        // Appointment Audit Log
        $logMessage = 'Added an Appointment.';
        $userId = $_SESSION['logged_id'] ?? null;
        $userType = $_SESSION['logged_role'] ?? 'Staff';

        $logstmt = $conn->prepare("INSERT INTO logs (user_id, user_type, log_message) VALUES (?, ?, ?)");
        if ($logstmt) {
            $logstmt->bind_param("iss", $userId, $userType, $logMessage);
            $logstmt->execute();
            $logstmt->close();
        }
    }

    // Commit Transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction completed successfully.',
        'duration_seconds' => $service_duration,
        'completed_appointment_id' => $completedAppointmentId
    ]);

} catch (Throwable $e) {
    $conn->rollback();
    error_log('Complete queue error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>