<?php
session_start();
header('Content-Type: application/json');
require_once_once '../php/connection.php'; 
require_once_once '../api/check-private.php';

if (!canManageRecords()) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Unauthorized action. Admin privileges require_onced.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
if (!isset($_SESSION['logged_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized session. Please log in again.']);
    exit;
}


$patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
$entered_password = isset($_POST['admin_password']) ? trim($_POST['admin_password']) : '';

if ($patient_id <= 0 || empty($entered_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing patient ID or password.']);
    exit;
}

$user_name = $_SESSION['logged_name'];

try {
    $stmt = $conn->prepare("SELECT password_hash FROM admin_auth WHERE username = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (!password_verify($entered_password, $row['password_hash'])) {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect authorization password.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User account not found.']);
        exit;
    }
    $stmt->close();

    $deleteStmt = $conn->prepare("DELETE FROM patient_records WHERE patient_id = ?");
    $deleteStmt->bind_param("i", $patient_id);

    if ($deleteStmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Record successfully deleted.']);
    } else {
        throw new Exception($deleteStmt->error);
    }
    $deleteStmt->close();

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>