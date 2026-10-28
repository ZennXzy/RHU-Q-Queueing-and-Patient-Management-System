<?php
require_once 'check-private.php';
authorizeRoles(['Doctor/Staff', 'Display','Patient']);
require_once '../php/connection.php';

header('Content-Type: application/json');

// Fetch active services where status = 'Active' from the services table
$activeServices = [];
$settingsQuery = $conn->query("SELECT service_name FROM services WHERE status = 'Active'");
if ($settingsQuery) {
    while ($sRow = $settingsQuery->fetch_assoc()) {
        $activeServices[] = $sRow['service_name'];
    }
}

// Fetch all services dynamically from the services table
$DEPARTMENT_CONFIG = [];
$allServicesQuery = $conn->query("SELECT service_id, service_name FROM services ORDER BY service_id ASC");
if ($allServicesQuery) {
    while ($row = $allServicesQuery->fetch_assoc()) {
        $DEPARTMENT_CONFIG[] = $row['service_name'];
    }
}

$responseData = [];

foreach ($DEPARTMENT_CONFIG as $serviceName) {
    $isVisible = in_array($serviceName, $activeServices);

    // Fetch In Progress queue
    $sqlActive = "SELECT queue_code FROM queue_information WHERE service_name = ? AND status = 'In Progress' ORDER BY created_at ASC";
    $stmtActive = $conn->prepare($sqlActive);
    $stmtActive->bind_param("s", $serviceName);
    $stmtActive->execute();
    $resActive = $stmtActive->get_result();
    $activeQueues = [];
    while ($row = $resActive->fetch_assoc()) {
        $activeQueues[] = $row['queue_code'];
    }
    $stmtActive->close();

    // Fetch Waiting queue
    $sqlWaiting = "SELECT queue_code FROM queue_information WHERE service_name = ? AND status = 'Waiting' ORDER BY created_at ASC";
    $stmtWaiting = $conn->prepare($sqlWaiting);
    $stmtWaiting->bind_param("s", $serviceName);
    $stmtWaiting->execute();
    $resWaiting = $stmtWaiting->get_result();
    $waitingQueues = [];
    while ($row = $resWaiting->fetch_assoc()) {
        $waitingQueues[] = $row['queue_code'];
    }
    $stmtWaiting->close();

    // Fetch Avg Waiting Time
    $sql = "SELECT service_duration FROM transactions WHERE service = ? AND DATE(transaction_date) = CURDATE() AND service_duration IS NOT NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $serviceName);
    $stmt->execute();
    $result = $stmt->get_result();

    $totalSeconds = 0;
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        preg_match_all('/\d+/', $row['service_duration'], $matches);
        if (!empty($matches[0]) && count($matches[0]) >= 3) {
            $totalSeconds += ((int)$matches[0][0] * 86400) + ((int)$matches[0][1] * 3600) + ((int)$matches[0][2] * 60);
            $count++;
        }
    }

    $avgFormatted = "00:00:00";
    if ($count > 0) {
        $avgSeconds = (int) round($totalSeconds / $count);
        $avgFormatted = sprintf('%02d:%02d:%02d', floor($avgSeconds / 3600), floor(($avgSeconds % 3600) / 60), $avgSeconds % 60);
    }
    $stmt->close();

    $responseData[] = [
        'service' => $serviceName,
        'isVisible' => $isVisible,
        'active' => $activeQueues,
        'waiting' => $waitingQueues,
        'avgTime' => $avgFormatted
    ];
}

echo json_encode(['status' => 'success', 'data' => $responseData]);
exit();