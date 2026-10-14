<?php
header('Content-Type: application/json');

// Ensure session is started and user is authorized as a Patient
require_once '../api/check-private.php';
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Retrieve patient ID securely from authenticated session
$patient_id =$_SESSION['patient_id'] ?? 0;
$service_name = isset($_POST['service']) ? trim($_POST['service']) : (isset($_POST['service_name']) ? trim($_POST['service_name']) : '');

// Optional PE findings & History
$blood_pressure   = isset($_POST['blood_pressure']) ? trim($_POST['blood_pressure']) : null;
$respiratory_rate = isset($_POST['respiratory_rate']) ? trim($_POST['respiratory_rate']) : null;
$temperature      = isset($_POST['temperature']) ? trim($_POST['temperature']) : null;
$pulse_rate       = isset($_POST['pulse_rate']) ? trim($_POST['pulse_rate']) : null;
$weight           = isset($_POST['weight']) ? trim($_POST['weight']) : null;
$height           = isset($_POST['height']) ? trim($_POST['height']) : null;
$description      = isset($_POST['description']) ? trim($_POST['description']) : null;

if ($patient_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or invalid patient session.']);
    exit;
}

if (empty($service_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Please select a valid health service.']);
    exit;
}

// Map service to prefix letter
switch ($service_name) {
    case 'Medical Consultation':
        $prefix = 'A';
        break;
    case 'Immunization':
        $prefix = 'B';
        break;
    case 'Pre-Natal':
        $prefix = 'C';
        break;
    case 'Medical Laboratory':
        $prefix = 'D';
        break;
    case 'Family Planning':
        $prefix = 'E';
        break;
    case 'Dental Examination/Tooth Extraction':
        $prefix = 'F';
        break;
    case 'Availment of anti-Tuberculosis Drugs':
        $prefix = 'G';
        break;
    case 'Child Care services':
        $prefix = 'H';
        break;
    case 'Availment of Tetanus Vaccine and Anti Rabies Vaccine':
        $prefix = 'I';
        break;
    default:
        $prefix = 'Z';
        break;
}

try {
    // Select the TRUE numerical MAXIMUM for this prefix created today (CURDATE())
    $code_sql = "SELECT MAX(CAST(SUBSTRING(queue_code, 2) AS UNSIGNED)) AS max_num 
                 FROM queue_information 
                 WHERE queue_code LIKE CONCAT(?, '%') 
                   AND DATE(`created_at`) = CURDATE()";

    $stmt_code =$conn->prepare($code_sql);$stmt_code->bind_param("s", $prefix);$stmt_code->execute();
    $code_result =$stmt_code->get_result();

    $next_number = 1;

    if ($row =$code_result->fetch_assoc()) {
        if (!is_null($row['max_num'])) {$next_number = intval($row['max_num']) + 1;         }     }$stmt_code->close();

    // Format new code (e.g. A0001, A0002)
    $queue_code = sprintf("%s%04d", $prefix, $next_number);
    $initial_status = 'Waiting';

    // Insert queue record with PE findings and description
    $insert_sql = "INSERT INTO queue_information 
                   (queue_code, patient_id, service_name, blood_pressure, respiratory_rate, temperature, pulse_rate, weight, height, diagnosis, status, `created_at`) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt_insert =$conn->prepare($insert_sql);$stmt_insert->bind_param(
        "sisssssssss", 
        $queue_code, 
        $patient_id,$service_name, 
        $blood_pressure,$respiratory_rate, 
        $temperature,$pulse_rate, 
        $weight,$height, 
        $description,$initial_status
    );

    if ($stmt_insert->execute()) {
        // Insert audit log
        $logStmt =$conn->prepare("INSERT INTO logs (log_message) VALUES (?)");
        if ($logStmt) {$logMsg = "Patient ID " . $patient_id . " created queue ticket " . $queue_code . " for " . $service_name . ".";
            $logStmt->bind_param("s", $logMsg);
            $logStmt->execute();$logStmt->close();
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Queue ticket created successfully.',
            'queue_code' => $queue_code
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