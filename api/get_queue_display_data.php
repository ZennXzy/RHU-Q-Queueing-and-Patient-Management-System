
<?php
require_once 'check-private.php';
authorizeRoles(['Doctor/Staff', 'Display', 'Patient']);
require_once '../php/connection.php';

date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json; charset=utf-8');

// --Active Services--
$activeServiceIds = [];
$settingsQuery = $conn->query("
    SELECT service_id
    FROM services
    WHERE status = 'Active'
");

if ($settingsQuery) {
    while ($row = $settingsQuery->fetch_assoc()) {
        $activeServiceIds[] = (int)$row['service_id'];
    }
}

// --Service Configuration--
$servicesConfig = [];
$allServicesQuery = $conn->query("
    SELECT service_id, service_name
    FROM services
    ORDER BY service_id ASC
");

if ($allServicesQuery) {
    while ($row = $allServicesQuery->fetch_assoc()) {
        $servicesConfig[] = [
            'id' => (int)$row['service_id'],
            'name' => $row['service_name']
        ];
    }
}

// --Format Duration--
function formatWaitingTime($seconds) {
    $seconds = max(0, (int)round($seconds));

    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    return sprintf(
        '%02d:%02d:%02d',
        $hours,
        $minutes,
        $remainingSeconds
    );
}

// --Retrieve Queue Data--
$responseData = [];

foreach ($servicesConfig as $service) {
    $serviceId = $service['id'];
    $serviceName = $service['name'];

    $isVisible = in_array(
        $serviceId,
        $activeServiceIds,
        true
    );

    // --In Progress Queue--
    $sqlActive = "
        SELECT queue_code
        FROM queue_information
        WHERE service_id = ?
          AND status = 'In Progress'
        ORDER BY created_at ASC
    ";

    $stmtActive = $conn->prepare($sqlActive);
    $stmtActive->bind_param("i", $serviceId);
    $stmtActive->execute();

    $resActive = $stmtActive->get_result();
    $activeQueues = [];

    while ($row = $resActive->fetch_assoc()) {
        $activeQueues[] = $row['queue_code'];
    }

    $stmtActive->close();

    // --Waiting Queue--
    $sqlWaiting = "
        SELECT queue_code
        FROM queue_information
        WHERE service_id = ?
          AND status = 'Waiting'
        ORDER BY created_at ASC
    ";

    $stmtWaiting = $conn->prepare($sqlWaiting);
    $stmtWaiting->bind_param("i", $serviceId);
    $stmtWaiting->execute();

    $resWaiting = $stmtWaiting->get_result();
    $waitingQueues = [];

    while ($row = $resWaiting->fetch_assoc()) {
        $waitingQueues[] = $row['queue_code'];
    }

    $stmtWaiting->close();

    // --Average Waiting Time Today--
    $sqlAverage = "
        SELECT AVG(service_duration) AS average_seconds
        FROM transactions
        WHERE service_id = ?
        AND transaction_date >= CURDATE()
        AND transaction_date < CURDATE() + INTERVAL 1 DAY
        AND service_duration IS NOT NULL
    ";

    $stmtAverage = $conn->prepare($sqlAverage);
    $stmtAverage->bind_param("i", $serviceId);
    $stmtAverage->execute();

    $averageResult = $stmtAverage->get_result();
    $averageRow = $averageResult->fetch_assoc();

    $averageSeconds = $averageRow['average_seconds'] ?? 0;
    $avgFormatted = formatWaitingTime($averageSeconds);

    $stmtAverage->close();

    // --Response Data--
    $responseData[] = [
        'service' => $serviceName,
        'isVisible' => $isVisible,
        'active' => $activeQueues,
        'waiting' => $waitingQueues,
        'avgTime' => $avgFormatted
    ];
}

echo json_encode([
    'status' => 'success',
    'data' => $responseData
]);

$conn->close();
exit;
?>
