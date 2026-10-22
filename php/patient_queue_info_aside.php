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

// Fixed SQL syntax: Added parentheses to the IN clause
$stmt = $conn->prepare("SELECT * FROM queue_information WHERE patient_id = ? ORDER BY created_at DESC LIMIT 1");

if ($stmt) {
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
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
    echo json_encode(['status' => 'error', 'message' => 'Database query preparation failed: ' . $conn->error]);
}