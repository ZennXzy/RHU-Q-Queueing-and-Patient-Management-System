<?php
header('Content-Type: application/json');
require_once '../php/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$patient_id   = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
$service_name = isset($_POST['service_name']) ? trim($_POST['service_name']) : '';

if ($patient_id <= 0 || empty($service_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Please select both a valid patient and service.']);
    exit;
}

$prefix = 'A'; 
switch (strtolower($service_name)) {
    case 'medical consultation':
        $prefix = 'A';
        break;
    case 'prenatal checkup':
        $prefix = 'B';
        break;
    case 'immunization / vaccination':
        $prefix = 'C';
        break;
    case 'dental checkup':
        $prefix = 'D';
        break;
    case 'laboratory request':
        $prefix = 'E';
        break;
    default:
        $prefix = 'Z';
        break;
}

try {
    $today = date('Y-m-d');
    $code_sql = "SELECT queue_code 
                 FROM queue_information 
                 WHERE queue_code LIKE CONCAT(?, '%') 
                   AND DATE(`created_at`) = ? 
                 ORDER BY queue_info_id DESC 
                 LIMIT 1";

    $stmt_code = $conn->prepare($code_sql);
    $stmt_code->bind_param("ss", $prefix, $today);
    $stmt_code->execute();
    $code_result = $stmt_code->get_result();

    $next_number = 1;

    if ($row = $code_result->fetch_assoc()) {
        $last_code = $row['queue_code'];
        $numeric_part = intval(substr($last_code, 1));
        $next_number = $numeric_part + 1;
    }
    $stmt_code->close();

    $queue_code = sprintf("%s%04d", $prefix, $next_number);

    $initial_status = 'Waiting';

    $insert_sql = "INSERT INTO queue_information (queue_code, patient_id, service_name, status, `created_at`) 
                  VALUES (?, ?, ?, ?, NOW())";

    $stmt_insert = $conn->prepare($insert_sql);
    $stmt_insert->bind_param("siss", $queue_code, $patient_id, $service_name, $initial_status);

    if ($stmt_insert->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Patient added to queue successfully.',
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