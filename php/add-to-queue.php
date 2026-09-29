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

    $stmt_code = $conn->prepare($code_sql);
    $stmt_code->bind_param("s", $prefix);
    $stmt_code->execute();
    $code_result = $stmt_code->get_result();

    $next_number = 1;

    if ($row = $code_result->fetch_assoc()) {
        if (!is_null($row['max_num'])) {
            $next_number = intval($row['max_num']) + 1;
        }
    }
    $stmt_code->close();

    // Format new code (e.g. A0001, A0002)
    $queue_code = sprintf("%s%04d", $prefix, $next_number);
    $initial_status = 'Waiting';

    // Insert record with MySQL's NOW()
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