<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';

if (isset($_GET['action']) && $_GET['action'] === 'details' && isset($_GET['id'])) {

    $account_id = intval($_GET['id']);
    
    // Read role directly from $_GET
    $role_type = strtolower(trim($_GET['role'] ?? ''));

    // Check if an admin account exists in admin_auth
    $adminCheck = $conn->query("SELECT id FROM admin_auth WHERE role = 'Admin' LIMIT 1");
    $hasAdmin = ($adminCheck && $adminCheck->num_rows > 0);

    if ($role_type === 'patient') {
        // Fetch patient details strictly from patient_auth
        $stmt = $conn->prepare("SELECT pa.*, 
                                TRIM(CONCAT(
                                    p.firstname, ' ', 
                                    COALESCE(CONCAT(p.middlename, ' '), ''), 
                                    p.lastname, 
                                    COALESCE(CONCAT(' ', p.suffix), '')
                                )) AS fullname,
                                TRIM(p.email) AS email
                            FROM patient_auth pa
                            JOIN patient_records p ON pa.patient_id = p.patient_id
                            WHERE pa.patient_auth_id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($account = $result->fetch_assoc()) {
            unset($account['password'], $account['password_hash']);
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
                'message' => 'Staff account not found.'
            ]);
        }
        $stmt->close();
    }
    
    $conn->close();
    exit;
}
?>