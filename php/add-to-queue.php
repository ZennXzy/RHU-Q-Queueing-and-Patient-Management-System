<?php
header('Content-Type: application/json');
require_once '../php/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
date_default_timezone_set('Asia/Manila');

$patient_id       = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
$service_name     = isset($_POST['service_name']) ? trim($_POST['service_name']) : '';
$blood_pressure   = isset($_POST['blood_pressure']) ? trim($_POST['blood_pressure']) : '';
$respiratory_rate = isset($_POST['respiratory_rate']) ? trim($_POST['respiratory_rate']) : '';
$temperature      = isset($_POST['temperature']) ? trim($_POST['temperature']) : '';
$pulse_rate       = isset($_POST['pulse_rate']) ? trim($_POST['pulse_rate']) : '';
$weight           = isset($_POST['weight']) ? trim($_POST['weight']) : '';
$height           = isset($_POST['height']) ? trim($_POST['height']) : '';
$complaints       = isset($_POST['complaints']) ? trim($_POST['complaints']) : '';

if ($patient_id <= 0 || empty($service_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Please select both a valid patient and service.']);
    exit;
}

$service_id = 0;

if ($service_name === '') {
    header('Location: ../pages/queue_manager_page.php?msg=' . urlencode('Please select a service') . '&type=error');
    exit();
} else {
    // Ensure the table and column names match your database schema
    $serviceCheck = $conn->prepare("SELECT service_id FROM services WHERE service_name = ? LIMIT 1");
    $serviceCheck->bind_param("s", $service_name);
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
}

try {
    // 1. Fetch all services ordered by creation/ID to dynamically determine the prefix letter based on position
    $services_res = $conn->query("SELECT service_name FROM services ORDER BY service_id ASC");
    $prefix = 'Z'; // Default fallback if not found
    $index = 0;

    if ($services_res && $services_res->num_rows > 0) {
        while ($row = $services_res->fetch_assoc()) {
            if (strcasecmp($row['service_name'], $service_name) === 0) {
                // Map index 0->A, 1->B, 2->C, etc. (up to 26 letters)
                if ($index < 26) {
                    $prefix = chr(65 + $index); // 65 is ASCII for 'A'
                }
                break;
            }
            $index++;
        }
    }

    // Get patient name for the response badge
    $p_stmt =$conn->prepare("SELECT CONCAT(firstname, ' ', lastname, ' ', COALESCE(suffix, '')) AS fullname FROM patient_records WHERE patient_id = ?");
    $p_stmt->bind_param("i", $patient_id);
    $p_stmt->execute();
    $p_res = $p_stmt->get_result();
    $patient_name = ($p_row =$p_res->fetch_assoc()) ? trim($p_row['fullname']) : "Patient #{$patient_id}";
    $p_stmt->close();

    // Select the TRUE numerical MAXIMUM for this prefix created today (CURDATE())
    $code_sql = "SELECT MAX(CAST(SUBSTRING(queue_code, 2) AS UNSIGNED)) AS max_num 
                 FROM queue_information 
                 WHERE queue_code LIKE CONCAT(?, '%') 
                   AND DATE(`created_at`) = CURDATE()";

    $stmt_code =$conn->prepare($code_sql);
    $stmt_code->bind_param("s", $prefix);
    $stmt_code->execute();
    $code_result =$stmt_code->get_result();

    $next_number = 1;
    if ($row =$code_result->fetch_assoc()) {
        if (!is_null($row['max_num'])) {
            $next_number = intval($row['max_num']) + 1;
        }     
    }
    $stmt_code->close();

    $queue_code = sprintf("%s%03d", $prefix,$next_number);
    $initial_status = 'Waiting';
    $current_datetime = date('F j, Y, g:i A');

    // Insert queue record with vital signs and complaints
    $insert_sql = "INSERT INTO queue_information 
                   (queue_code, patient_id, service_name, blood_pressure, respiratory_rate, temperature, pulse_rate, weight, height, diagnosis, status, `created_at`) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt_insert =$conn->prepare($insert_sql);$stmt_insert->bind_param(
        "siissssssss", 
        $queue_code, $patient_id,$service_id, 
        $blood_pressure,$respiratory_rate, $temperature,$pulse_rate, $weight,$height, $complaints,$initial_status
    );

    if ($stmt_insert->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Patient added to queue successfully.',
            'queue_code' => $queue_code,
            'patient_name' => $patient_name,
            'service_name' => $service_name,
            'datetime' => $current_datetime
        ]);
    } else {
        throw new Exception($stmt_insert->error);
    }

    $stmt_insert->close();

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>