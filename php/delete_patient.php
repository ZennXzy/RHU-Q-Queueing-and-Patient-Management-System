<?php
// php/delete_patient.php
session_start();
header('Content-Type: application/json');

require_once '../php/connection.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
$entered_password = isset($_POST['admin_password']) ? trim($_POST['admin_password']) : '';

if ($patient_id <= 0 || empty($entered_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing patient ID or password.']);
    exit;
}

// 1. VERIFY LOGGED-IN USER SESSION
if (!isset($_SESSION['logged_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized session. Please log in again.']);
    exit;
}

$user_id = $_SESSION['logged_id'];

try {
    // 2. FETCH STORED PASSWORD HASH FOR CURRENT USER
    $stmt = $conn->prepare("SELECT password_hash FROM admin_auth WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verify provided password against hashed password in DB
        if (!password_verify($entered_password, $row['password_hash'])) {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect authorization password.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User account not found.']);
        exit;
    }
    $stmt->close();

    // 3. EXECUTE PATIENT RECORD DELETION
    $deleteStmt = $conn->prepare("DELETE FROM patients_record WHERE patient_id = ?");
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