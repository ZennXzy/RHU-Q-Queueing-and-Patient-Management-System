<?php
session_start();
header('Content-Type: application/json');
require_once_once '../php/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$allServices = [
    'Medical Consultation',
    'Immunization',
    'Pre-Natal',
    'Medical Laboratory',
    'Family Planning',
    'Dental Examination/Tooth Extraction',
    'Availment of anti-Tuberculosis Drugs',
    'Child Care services',
    'Availment of Tetanus Vaccine and Anti Rabies Vaccine'
];

$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true) ?? $_POST;

$selectedServices = $data['services'] ?? [];

try {
    $conn->begin_transaction();

    // Prepared statement checking if service_type already exists
    $checkStmt = $conn->prepare("SELECT queue_display_id FROM queue_display WHERE service_type = ? LIMIT 1");
    $updateStmt = $conn->prepare("UPDATE queue_display SET status = ?, date_created = CURRENT_TIMESTAMP WHERE service_type = ?");
    $insertStmt = $conn->prepare("INSERT INTO queue_display (service_type, status) VALUES (?, ?)");

    foreach ($allServices as $service) {
        $status = in_array($service, $selectedServices) ? 'Active' : 'Inactive';

        // Check if row already exists
        $checkStmt->bind_param("s", $service);
        $checkStmt->execute();
        $res = $checkStmt->get_result();

        if ($res && $res->num_rows > 0) {
            // Update existing record without creating duplicates
            $updateStmt->bind_param("ss", $status, $service);
            $updateStmt->execute();
        } else {
            // Insert only if record does not exist yet
            $insertStmt->bind_param("ss", $service, $status);
            $insertStmt->execute();
        }
    }

    $checkStmt->close();
    $updateStmt->close();
    $insertStmt->close();

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