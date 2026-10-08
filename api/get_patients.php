<?php
header('Content-Type: application/json');
require_once_once '../php/connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['action']) && $_GET['action'] === 'details') {
    // Fallback to active logged-in session patient_id if id is set to "session" or missing
    $patient_id = $_GET['id'] ?? null;

    if ($patient_id === 'session' || empty($patient_id)) {
        $patient_id = $_SESSION['patient_id'] ?? $_SESSION['user_id'] ?? null;
    }

    if (!$patient_id) {
        echo json_encode(['status' => 'error', 'message' => 'No patient ID provided']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM patient_records WHERE patient_id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($patient = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'patient' => $patient]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Patient record not found']);
    }
    exit;
}
?>