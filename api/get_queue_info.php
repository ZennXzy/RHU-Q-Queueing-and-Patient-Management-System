<?php
require_once '../api/check-private.php';
authorizeRoles(['Admin', 'Doctor/Staff']);
require_once '../php/connection.php';

header('Content-Type: application/json');

try {
    $queueInfoId = isset($_GET['queue_info_id']) ? intval($_GET['queue_info_id']) : 0;

    if ($queueInfoId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid queue ID provided.']);
        exit;
    }

    // Query queue information combined with patient full name
    $sql = "SELECT 
                q.queue_info_id, 
                q.queue_code, 
                s.service_name, 
                q.blood_pressure, 
                q.respiratory_rate, 
                q.temperature, 
                q.pulse_rate, 
                q.weight, 
                q.height, 
                q.diagnosis, 
                q.created_at,
                p.firstname, 
                p.lastname,
                p.suffix
            FROM queue_information q
            INNER JOIN patient_records p ON q.patient_id = p.patient_id
            LEFT JOIN services s ON q.service_name = s.service_id
            WHERE q.queue_info_id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Database prepare error: " . $conn->error);
    }

    $stmt->bind_param("i", $queueInfoId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $fullName = ucfirst($row['lastname']) . ', ' . ucfirst($row['firstname']) . ($row['suffix'] ? ' ' . ucfirst($row['suffix']) : '');

        echo json_encode([
            'status' => 'success',
            'queue' => [
                'queue_code' => $row['queue_code'],
                'patient_name' => $fullName,
                'service_name' => $row['service_name'],
                'created_at' => $row['created_at'],
                'blood_pressure' => $row['blood_pressure'],
                'respiratory_rate' => $row['respiratory_rate'],
                'temperature' => $row['temperature'],
                'pulse_rate' => $row['pulse_rate'],
                'weight' => $row['weight'],
                'height' => $row['height'],
                'complaints' => $row['diagnosis']
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Queue record not found.']);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>