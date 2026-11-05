
<?php
session_start();
header('Content-Type: application/json');

require_once '../php/connection.php';
require_once '../api/check-private.php';

authorizeRoles(['Patient']);
date_default_timezone_set('Asia/Manila');

$patient_id = (int)($_SESSION['patient_id'] ?? 0);

try {
    if ($patient_id <= 0) {
        throw new Exception('Unauthorized session.');
    }

    $today = date('Y-m-d');

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS due_count
        FROM appointments
        WHERE patient_id = ?
        AND status = 'Approved'
        AND appointment_date < DATE_ADD(?, INTERVAL 1 DAY)
    ");

    if (!$stmt) {
        throw new Exception('Failed to prepare appointment query.');
    }

    $stmt->bind_param('is', $patient_id, $today);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $due_count = (int)($result['due_count'] ?? 0);

    echo json_encode([
        'status' => 'success',
        'has_due_appointment' => $due_count > 0,
        'due_count' => $due_count
    ]);

} catch (Throwable $e) {
    error_log('Due appointment check: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to check appointments.'
    ]);
}

$conn->close();
?>
