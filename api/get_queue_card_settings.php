<?php
header('Content-Type: application/json');
require_once_once '../php/connection.php';

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

try {
    $sql = "SELECT queue_display_id, service_type, status FROM queue_display";
    $result = $conn->query($sql);

    // Seed table with default entries if empty
    if ($result && $result->num_rows === 0) {
        $insertStmt = $conn->prepare("INSERT INTO queue_display (service_type, status) VALUES (?, 'Active')");
        foreach (array_slice($allServices, 0, 5) as $service) {
            $insertStmt->bind_param("s", $service);
            $insertStmt->execute();
        }
        $insertStmt->close();
        $result = $conn->query($sql);
    }

    $settings = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $settings[] = $row;
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => $settings
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>