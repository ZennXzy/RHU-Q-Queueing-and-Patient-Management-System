
<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../php/connection.php';
require_once '../api/check-private.php';

authorizeRoles(['Patient']);

$patient_id = (int)($_SESSION['patient_id'] ?? 0);
$appointment_id = (int)($_POST['appointment_id'] ?? 0);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    if ($patient_id <= 0 || $appointment_id <= 0) {
        throw new Exception('Invalid patient session or appointment.');
    }

    $stmt = $conn->prepare("
        DELETE FROM appointments
        WHERE appointments_id = ?
        AND patient_id = ?
    ");

    if (!$stmt) {
        throw new Exception('Failed to prepare appointment removal.');
    }

    $stmt->bind_param('ii', $appointment_id, $patient_id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to remove appointment.');
    }

    if ($stmt->affected_rows !== 1) {
        throw new Exception('Appointment not found or already removed.');
    }

    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Appointment removed successfully.'
    ]);

} catch (Throwable $e) {
    error_log('Patient appointment removal: ' . $e->getMessage());

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
