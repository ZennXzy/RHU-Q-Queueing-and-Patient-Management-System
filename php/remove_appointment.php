
<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../php/connection.php';
require_once '../api/check-private.php';

authorizeRoles(['Admin', 'Doctor/Staff']);
date_default_timezone_set('Asia/Manila');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Invalid request method.');
    }

    $appointmentId = filter_var($_POST['appointment_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$appointmentId || $appointmentId < 1) {
        http_response_code(400);
        throw new Exception('Invalid appointment ID.');
    }

    $stmt = $conn->prepare("UPDATE appointments SET status = 'Queued' WHERE appointments_id = ? AND status = 'Approved'");
    if (!$stmt) throw new Exception('Unable to prepare appointment update.');

    $stmt->bind_param("i", $appointmentId);
    if (!$stmt->execute()) throw new Exception('Unable to update appointment status.');

    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected !== 1) {
        http_response_code(409);
        throw new Exception('Appointment is not approved or has already been queued.');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Appointment status updated to Queued.',
        'appointment_id' => $appointmentId,
        'appointment_status' => 'Queued'
    ]);
} catch (Throwable $e) {
    if (http_response_code() === 200) http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>
