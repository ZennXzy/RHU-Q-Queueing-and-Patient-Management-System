<?php
require_once '../api/check-private.php';
require_once '../php/connection.php';

// Enforce Patient Authorization
authorizeRoles(['Patient']);

$patient_id = $_SESSION['patient_id'] ?? null;
header('Content-Type: application/json');

if (!$patient_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized patient session.']);
    exit();
}

// SQL query computes position_ahead specifically within the same service_type
$stmt = $conn->prepare("
    SELECT q.*, 
           (SELECT COUNT(*) FROM queue_information q2 
            WHERE q2.status = 'Waiting' 
              AND q2.service_name = q.service_name
              AND q2.created_at < q.created_at) as position_ahead
    FROM queue_information q 
    WHERE q.patient_id = ? AND q.status IN ('Waiting', 'In Progress') 
    ORDER BY q.created_at DESC 
    LIMIT 1
");

if ($stmt) {
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $statusText = $row['status'];
        $positionAhead = intval($row['position_ahead']);

        if ($row['status'] === 'Waiting') {
            if ($positionAhead === 0) {
                $statusText = 'Next in Line';
            } else {
                $statusText = "Waiting ($positionAhead ahead)";
            }
        } elseif ($row['status'] === 'In Progress') {
            $statusText = 'Serving';
        }

        $row['status'] = $statusText;

        echo json_encode([
            'status' => 'success',
            'hasQueue' => true,
            'queue' => $row
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'hasQueue' => false
        ]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database query preparation failed.']);
}