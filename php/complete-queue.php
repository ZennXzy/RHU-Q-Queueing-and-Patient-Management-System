<?php
header('Content-Type: application/json');
session_start();
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$queue_info_id    = isset($_POST['queue_info_id']) ? intval($_POST['queue_info_id']) : 0;
$patient_name     = isset($_POST['patient_name']) ? trim($_POST['patient_name']) : '';
$queue_code       = isset($_POST['queue_code']) ? trim($_POST['queue_code']) : '';
$service          = isset($_POST['service']) ? trim($_POST['service']) : '';
$remarks          = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
$appointment      = isset($_POST['add_appointment']) ? intval($_POST['add_appointment']) : 0;
$appointment_date = isset($_POST['appointment_date']) ? trim($_POST['appointment_date']) : '';

$server_name = $_SESSION['logged_id'] ?? '';

if ($queue_info_id <= 0 || trim($patient_name) === '') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters provided.']);
    exit;
}


$service_id = 0;

$serviceCheck = $conn->prepare("SELECT service_id FROM services WHERE service_name = ? LIMIT 1");
$serviceCheck->bind_param("s", $service);
$serviceCheck->execute();
$serviceRes = $serviceCheck->get_result();

if ($serviceRes->num_rows === 0) {
    $serviceCheck->close();
    header('Location: ../pages/queue_manager_page.php?msg=' . urlencode('Please select a valid service from the list.') . '&type=error');
    exit();
} else {
    // FIX: Fetch the row into an array first
    $row = $serviceRes->fetch_assoc();
    $service_id = $row['service_id'];
}
$serviceCheck->close();


$conn->begin_transaction();

try {
    $patient_id = NULL;
    $patient_contact = NULL;

    $get_id = $conn->prepare("SELECT patient_id FROM queue_information WHERE queue_info_id = ? ");
    $get_id->bind_param("i", $queue_info_id);
    $get_id->execute();
    
    $result_get_id = $get_id->get_result();
    if ($row = $result_get_id->fetch_assoc()) {
        $patient_id = intval($row['patient_id']);
    }
    $get_id->close();

    $get_contact = $conn->prepare("SELECT contact_number FROM patient_records WHERE patient_id = ? ");
    $get_contact->bind_param("i", $patient_id);
    $get_contact->execute();
    
    $result_get_contact = $get_contact->get_result();
    if ($row = $result_get_contact->fetch_assoc()) {
        $patient_contact = $row['contact_number'];
    }
    $get_contact->close();


    $service_time  = NULL;
    $accepted_time = NULL;

    $update_sql = "UPDATE queue_information SET status = 'Completed', service_duration = NOW() WHERE queue_info_id = ?";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("i", $queue_info_id);

    if (!$stmt_update->execute()) {
        throw new Exception("Update queue status failed: " . $stmt_update->error);
    }
    $stmt_update->close();

    $getTime = "SELECT accepted_at, service_duration FROM queue_information WHERE queue_info_id = ?";
    $stmt_get = $conn->prepare($getTime);
    $stmt_get->bind_param("i", $queue_info_id);
    $stmt_get->execute();

    $result = $stmt_get->get_result();
    if ($row = $result->fetch_assoc()) {
        $accepted_time = $row['accepted_at'];
        $service_time  = $row['service_duration'];
    }
    $stmt_get->close();

    $start = new DateTime($accepted_time ?? 'now');
    $end   = new DateTime($service_time ?? 'now');
    $interval = $start->diff($end);
    $totalSeconds = $end->getTimestamp() - $start->getTimestamp();
    $service_duration = $interval->format('%d days, %h hours, %i minutes');

    $insert_sql = "INSERT INTO transactions (patient_id, queue_code, service, remarks, server_name, service_duration, transaction_date) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt_insert = $conn->prepare($insert_sql);
    if (!$stmt_insert) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt_insert->bind_param("isisis", $patient_id, $queue_code, $service_id, $remarks, $server_name, $service_duration);
    
    if (!$stmt_insert->execute()) {
        throw new Exception("Insert transaction failed: " . $stmt_insert->error);
    } else {
        $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
        if ($insertlog) {
            $message = $_SESSION['logged_account']. " completed a task.";
            $insertlog->bind_param('s', $message);
            $insertlog->execute();
            $insertlog->close();
        }
    }
    $stmt_insert->close();
    if ($appointment === 1 && !empty($appointment_date)) {
        $appointment_status = 'Approved';

        $appointment_sql = "INSERT INTO appointments (patient_id, contact_number, service, remarks, server_name, status, appointment_date, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt_app = $conn->prepare($appointment_sql);
        if (!$stmt_app) {
            throw new Exception("Prepare appointment failed: " . $conn->error);
        }

        $stmt_app->bind_param("isssiss", $patient_id, $patient_contact, $service, $remarks, $server_name, $appointment_status, $appointment_date);

        if (!$stmt_app->execute()) {
            throw new Exception("Insert appointment failed: " . $stmt_app->error);
        } else {
            $insertlog = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
            if ($insertlog) {
                $message = $_SESSION['logged_name'] . " added an Appointment.";
                $insertlog->bind_param('s', $message);
                $insertlog->execute();
                $insertlog->close();
            }
        }
        $stmt_app->close();
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction completed successfully.',
        'duration' => $totalSeconds
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>