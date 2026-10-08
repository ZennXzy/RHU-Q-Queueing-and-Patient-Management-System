<?php
session_start();
header('Content-Type: application/json');
require_once_once '../php/connection.php';
require_once_once '../api/check-private.php';

try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS pending_count FROM appointments WHERE status = 'Pending'");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    $pendingCount = (int)($result['pending_count'] ?? 0);

    echo json_encode([
        'status'  => 'success',
        'hasPending' => $pendingCount > 0,
        'count'   => $pendingCount
    ]);
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>