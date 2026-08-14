<?php
// php/get_patients.php
header('Content-Type: application/json');
require_once '../php/connection.php';

if (isset($_GET['action']) && $_GET['action'] === 'details' && isset($_GET['id'])) {
    $patient_id = $_GET['id'];
    
    // Make sure to query ALL columns
    $stmt = $conn->prepare("SELECT * FROM patient_records WHERE patient_id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($patient = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'patient' => $patient]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Patient not found']);
    }
    exit;
}
?>