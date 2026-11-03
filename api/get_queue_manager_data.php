<?php
require_once '../api/check-private.php';
authorizeRoles(['Admin', 'Doctor/Staff']);
require_once '../php/connection.php';

header('Content-Type: application/json');

try {
    // 1. Fetch Waiting Patients
    $waitingSql = "SELECT 
                    q.queue_info_id, 
                    q.queue_code, 
                    q.patient_id, 
                    s.service_name, 
                    q.created_at,
                    p.firstname, 
                    p.lastname,
                    p.suffix
                FROM queue_information q
                INNER JOIN patient_records p ON q.patient_id = p.patient_id
                LEFT JOIN services s ON q.service_name = s.service_id
                WHERE q.status = 'Waiting' 
                ORDER BY q.created_at ASC";
    $waitingRes = $conn->query($waitingSql);
    $waitingPatients = [];
    while ($row = $waitingRes->fetch_assoc()) {
        $fullName = ucfirst($row['lastname']) . ', ' . ucfirst($row['firstname']) . ($row['suffix'] ? ' ' . ucfirst($row['suffix']) : '');
        $waitingPatients[] = [
            'queue_info_id' => $row['queue_info_id'],
            'queue_code' => $row['queue_code'],
            'patient_name' => $fullName,
            'service_name' => $row['service_name'] ?? 'General Consultation'
        ];
    }

    // 2. Fetch In Progress Patients
    $inProgressSql = "SELECT 
                    q.queue_info_id, 
                    q.queue_code, 
                    s.service_name, 
                    q.accepted_at,
                    p.firstname, 
                    p.lastname,
                    p.suffix
                FROM queue_information q
                INNER JOIN patient_records p ON q.patient_id = p.patient_id
                LEFT JOIN services s ON q.service_name = s.service_id
                WHERE q.status = 'In Progress' 
                ORDER BY q.accepted_at ASC";
    $inProgressRes = $conn->query($inProgressSql);
    $inProgressPatients = [];
    while ($row = $inProgressRes->fetch_assoc()) {
        $fullName = ucfirst($row['lastname']) . ', ' . ucfirst($row['firstname']) . ($row['suffix'] ? ' ' . ucfirst($row['suffix']) : '');
        $inProgressPatients[] = [
            'queue_info_id' => $row['queue_info_id'],
            'queue_code' => $row['queue_code'],
            'patient_name' => $fullName,
            'service_name' => $row['service_name'] ?? 'General Consultation'
        ];
    }

    // 3. Fetch Dashboard Counts
    $waitingCount = $conn->query("SELECT COUNT(*) AS cnt FROM queue_information WHERE status = 'Waiting'")->fetch_assoc()['cnt'];
    $inProgressCount = $conn->query("SELECT COUNT(*) AS cnt FROM queue_information WHERE status = 'In Progress'")->fetch_assoc()['cnt'];
    $completedCount = $conn->query("SELECT COUNT(*) AS cnt FROM queue_information WHERE status = 'Completed' AND DATE(created_at) = CURDATE()")->fetch_assoc()['cnt'];

    echo json_encode([
        'status' => 'success',
        'waiting' => $waitingPatients,
        'in_progress' => $inProgressPatients,
        'counts' => [
            'waiting' => $waitingCount,
            'in_progress' => $inProgressCount,
            'completed' => $completedCount
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
$conn->close();
?>