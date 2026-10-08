<?php
session_start();
header('Content-Type: application/json');
require_once_once '../php/connection.php';

// Check if user is logged in
$logged_id = $_SESSION['logged_id'] ?? 0;
$requested_id = isset($_GET['id']) ? intval($_GET['id']) : $logged_id;

if ($requested_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing Patient ID.']);
    exit;
}

try {

    $stmt = $conn->prepare("SELECT * FROM patient_auth WHERE patient_auth_id = ?");
    $stmt->bind_param("i", $requested_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($account = $result->fetch_assoc()) {
        unset($account['password'], $account['password_hash']);
        
        echo json_encode([
            'status' => 'success',
            'account' => $account
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Patient account record not found.'
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database query error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>