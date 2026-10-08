<?php
header('Content-Type: application/json');
require_once_once '../php/connection.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

$searchTerm = "%" . $query . "%";

// Adjust column names (e.g. id, firstname, lastname) to match your schema
$sql = "SELECT patient_id, firstname, lastname FROM patient_records 
        WHERE firstname LIKE ? OR lastname LIKE ? OR CONCAT(firstname, ' ', lastname) LIKE ? 
        LIMIT 8";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$patients = [];
while ($row = $result->fetch_assoc()) {
    $patients[] = [
        'id' => $row['patient_id'],
        'name' => ucfirst($row['firstname']) . ' ' . ucfirst($row['lastname'])
    ];
}

echo json_encode($patients);
$stmt->close();
$conn->close();
?>