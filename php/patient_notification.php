<?php
require_once '../api/check-private.php';
require_once '../php/connection.php';

// Enforce Patient Authorization
authorizeRoles(['Admin','Doctor/Staff','Patient']);

$patient_id = $_SESSION['patient_id'] ?? null;
header('Content-Type: application/json');

if (!$patient_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized patient session.']);
    exit();
}

// SQL query joins the services table since q.service_name now holds the integer service_id
$stmt = $conn->prepare("
    SELECT q.*, s.service_name as resolved_service_name,
           (SELECT COUNT(*) FROM queue_information q2 
            WHERE q2.status = 'Waiting' 
              AND q2.service_name = q.service_name
              AND q2.created_at < q.created_at) as position_ahead
    FROM queue_information q 
    LEFT JOIN services s ON q.service_name = s.service_id
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

        // Replace the numeric service ID with the readable service name for frontend display
        if (!empty($row['resolved_service_name'])) {
            $row['service_name'] = $row['resolved_service_name'];
        } elseif (!empty($row['service_snapshot'])) {
            $row['service_name'] = $row['service_snapshot'];
        }

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
?>