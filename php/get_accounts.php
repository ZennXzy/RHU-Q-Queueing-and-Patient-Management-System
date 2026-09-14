<?php
header('Content-Type: application/json');
require_once '../php/connection.php';

if (isset($_GET['action']) && $_GET['action'] === 'details' && isset($_GET['id'])) {
    $account_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM admin_auth WHERE id = ?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($account = $result->fetch_assoc()) {
        unset($account['password']); 

        echo json_encode(['status' => 'success', 'account' => $account]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Account not found']);
    }
    
    $stmt->close();
    $conn->close();
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing parameters']);
    exit;
}
?>