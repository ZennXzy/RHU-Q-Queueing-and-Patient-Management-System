<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true) ?? $_POST;
$selectedServices = $data['services'] ?? [];

try {
    $conn->begin_transaction();

    // Reset all services to 'Inactive' first, then activate the selected ones
    $conn->query("UPDATE services SET status = 'Inactive'");

    if (!empty($selectedServices)) {
        $stmt = $conn->prepare("UPDATE services SET status = 'Active' WHERE service_name = ?");
        foreach ($selectedServices as $service) {
            $stmt->bind_param("s", $service);
            $stmt->execute();
        }
        $stmt->close();
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Display settings updated successfully.'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => 'Database update failed: ' . $e->getMessage()
    ]);
}

$conn->close();
?>