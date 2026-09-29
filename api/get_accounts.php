<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';

if (isset($_GET['action']) && $_GET['action'] === 'details' && isset($_GET['id'])) {
    $account_id = intval($_GET['id']);
    $role_type = $_GET['role'] ?? ($_SESSION['logged_role'] ?? '');

    $adminCheck = $conn->query("SELECT id FROM admin_auth WHERE role = 'Admin' LIMIT 1");
    $hasAdmin = ($adminCheck && $adminCheck->num_rows > 0);

    if ($role_type === 'Patient') {
        $stmt = $conn->prepare("SELECT * FROM patient_auth WHERE id = ? OR patient_auth_id = ?");
        $stmt->bind_param("ii", $account_id, $account_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($account = $result->fetch_assoc()) {
            unset($account['password']);
            $account['role'] = 'Patient';

            echo json_encode([
                'status' => 'success', 
                'account' => $account,
                'has_admin' => $hasAdmin
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Patient account not found']);
        }
        $stmt->close();
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin_auth WHERE id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($account = $result->fetch_assoc()) {
            unset($account['password']); 

            echo json_encode([
                'status' => 'success', 
                'account' => $account,
                'has_admin' => $hasAdmin
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Account not found']);
        }
        $stmt->close();
    }
    
    $conn->close();
    exit;
}
?>