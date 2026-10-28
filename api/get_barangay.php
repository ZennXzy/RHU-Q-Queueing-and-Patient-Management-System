<?php
header('Content-Type: application/json');
require_once '../php/connection.php';

$query = "SELECT barangay_address FROM barangay ORDER BY barangay_address ASC";
$result = $conn->query($query);

$barangays = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $barangays[] = $row['barangay_address'];
    }
}

echo json_encode([
    'status' => 'success',
    'barangays' => $barangays
]);
$conn->close();
?>