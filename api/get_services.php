<?php
header('Content-Type: application/json');
require_once '../api/check-private.php';
require_once '../php/connection.php';

$query = "SELECT * FROM services ORDER BY service_name ASC";
$result = $conn->query($query);

$services = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = [
            'service_id' => $row['service_id'],
            'service_name' => $row['service_name'],
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ];
    }
}

echo json_encode([
    'status' => 'success',
    'services' => $services
]);

$conn->close();
?>