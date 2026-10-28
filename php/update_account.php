<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php'; 
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;
$logged_id  = $_SESSION['logged_id'] ?? 0;
$logged_role = $_SESSION['logged_role'] ?? '';

if ($account_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing Account ID.']);
    exit;
}

// Security Check: Users can only edit their own account unless they are Admin
if ($logged_role !== 'Admin' && $logged_id !== $account_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized action.']);
    exit;
}

function get_post_val($key, $default = null) {
    return (isset($_POST[$key]) && trim($_POST[$key]) !== '') ? trim($_POST[$key]) : $default;
}

$username         = get_post_val('username');
$fullname         = get_post_val('fullname');
$email            = get_post_val('email');
$role             = get_post_val('role', $logged_role);
$current_password = get_post_val('current_password');
$new_password     = get_post_val('password');
$target_role      = get_post_val('target_role', $role);

$table = ($target_role === 'Patient') ? 'patient_auth' : 'admin_auth';
$id_field = ($target_role === 'Patient') ? 'patient_auth_id' : 'id';

// Verify field column existence for patient_auth
if ($target_role === 'Patient') {
    $checkCol = $conn->query("SHOW COLUMNS FROM patient_auth LIKE 'patient_auth_id'");
    if (!$checkCol || $checkCol->num_rows === 0) {
        $id_field = 'id';
    }
}

// Begin transaction for safe atomic updates
$conn->begin_transaction();

try {
    // Check if user attempts password update
    $hashed_password = null;
    if (!empty($new_password)) {
        if ($logged_role !== 'Admin' && empty($current_password)) {
            throw new Exception('Current password is required to change password.');
        }

        if ($logged_role !== 'Admin') {
            $passStmt = $conn->prepare("SELECT password_hash FROM {$table} WHERE {$id_field} = ?");
            $passStmt->bind_param("i", $account_id);
            $passStmt->execute();
            $res = $passStmt->get_result()->fetch_assoc();
            $passStmt->close();

            if (!$res || !password_verify($current_password, $res['password_hash'])) {
                throw new Exception('Incorrect current password.');
            }
        }

        $options = ["cost" => 12];
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, $options);
    }

    // Prepare update query based on target role
    if ($target_role === 'Patient') {
        if (!empty($hashed_password)) {
            $sql = "UPDATE patient_auth pa
                    JOIN patient_records pr ON pa.patient_id = pr.patient_id
                    SET pa.username = ?, 
                        pa.password_hash = ?, 
                        pr.email = ?
                    WHERE pa.patient_auth_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $username, $hashed_password, $email, $account_id);
        } else {
            $sql = "UPDATE patient_auth pa
                    JOIN patient_records pr ON pa.patient_id = pr.patient_id
                    SET pa.username = ?, 
                        pr.email = ?
                    WHERE pa.patient_auth_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $username, $email, $account_id);
        }
    } else {
        if (!empty($hashed_password)) {
            $sql = "UPDATE admin_auth SET username = ?, fullname = ?, email = ?, role = ?, password_hash = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $username, $fullname, $email, $role, $hashed_password, $account_id);
        } else {
            $sql = "UPDATE admin_auth SET username = ?, fullname = ?, email = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $username, $fullname, $email, $role, $account_id);
        }
    }

    if (!$stmt->execute()) {
        throw new Exception("Execution failed: " . $stmt->error);
    }
    $stmt->close();

    // Commit transaction
    $conn->commit();

    if ($logged_id === $account_id) {
        $_SESSION['logged_account'] = $fullname;
    }

    // Log activity
    $logMsg = "Account " . $username . " has been updated.";
    $logStmt = $conn->prepare('INSERT INTO logs (log_message) VALUES (?)');
    if ($logStmt) {
        $logStmt->bind_param('s', $logMsg);
        $logStmt->execute();
        $logStmt->close();
    }    

    echo json_encode([
        'status' => 'success',
        'message' => 'Account updated successfully.'
    ]);

} catch (Exception $e) {
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if ($conn->in_transaction) {
        $conn->rollback();
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>