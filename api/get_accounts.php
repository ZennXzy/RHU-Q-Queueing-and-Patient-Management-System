<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';

if (isset($_GET['action']) && $_GET['action'] === 'details' && isset($_GET['id'])) {

    $account_id = intval($_GET['id']);
    $role_type = $_GET['role'] ?? ($_SESSION['logged_role'] ?? $_SESSION['user_type'] ?? '');

    // Check if an admin account exists in admin_auth
    $adminCheck = $conn->query("SELECT id FROM admin_auth WHERE role = 'Admin' LIMIT 1");
    $hasAdmin = ($adminCheck && $adminCheck->num_rows > 0);

    if (strtolower($role_type) === 'patient') {

        // Fetch patient details strictly from patient_auth
        $stmt = $conn->prepare("SELECT * FROM patient_auth WHERE patient_id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($account = $result->fetch_assoc()) {
            // Remove sensitive password fields from output
            unset($account['password'], $account['password_hash']);
            
            // Explicitly ensure patient role is set
            $account['role'] = 'Patient';

            echo json_encode([
                'status' => 'success', 
                'account' => $account,
                'has_admin' => $hasAdmin
            ]);
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Patient account not found.'
            ]);
        }
        $stmt->close();
    } else {
        // Fetch staff / admin details from admin_auth
        $stmt = $conn->prepare("SELECT * FROM admin_auth WHERE id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($account = $result->fetch_assoc()) {
            unset($account['password'], $account['password_hash']); 

            echo json_encode([
                'status' => 'success', 
                'account' => $account,
                'has_admin' => $hasAdmin
            ]);
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Account not found.'
            ]);
        }
        $stmt->close();
    }
    
    $conn->close();
    exit;
}
?>