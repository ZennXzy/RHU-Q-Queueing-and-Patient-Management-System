<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';

if (!isset($_SESSION['logged_role']) || $_SESSION['logged_role'] !== 'Admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized action.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'] ?? '';
$service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
$service_name = trim($_POST['service_name'] ?? '');

try {
    if ($action === 'add') {
        $service_status = "Inactive";
        if (empty($service_name)) {
            throw new Exception('Service name cannot be empty.');
        }
        $stmt = $conn->prepare("INSERT INTO services (service_name, status) VALUES (?, ?)");
        $stmt->bind_param("ss", $service_name, $service_status);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Service added successfully.']);

    } elseif ($action === 'edit') {
        if ($service_id <= 0 || empty($service_name)) {
            throw new Exception('Invalid service data provided.');
        }
        $stmt = $conn->prepare("UPDATE services SET service_name = ? WHERE service_id = ?");
        $stmt->bind_param("si", $service_name, $service_id);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Service updated successfully.']);

    } elseif ($action === 'delete') {
        if ($service_id <= 0) {
            throw new Exception('Invalid service ID.');
        }
        $stmt = $conn->prepare("DELETE FROM services WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Service deleted successfully.']);
    } else {
        throw new Exception('Unknown action.');
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>