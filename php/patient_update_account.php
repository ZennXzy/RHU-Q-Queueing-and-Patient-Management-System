<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$account_id  = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;
$logged_id   = $_SESSION['logged_id'] ?? $_SESSION['patient_id'] ?? 0;
$logged_role = $_SESSION['logged_role'] ?? $_SESSION['user_type'] ?? '';

// Security Check: Ensure user is logged in as a Patient and modifying their own account
if (strtolower($logged_role) !== 'patient' || $logged_id <= 0 || $logged_id !== $account_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized action.']);
    exit;
}

function get_post_val($key, $default = null) {
    return (isset($_POST[$key]) && trim($_POST[$key]) !== '') ? trim($_POST[$key]) : $default;
}

$username         = get_post_val('username');
$fullname         = get_post_val('fullname');
$email            = get_post_val('email');
$current_password = get_post_val('current_password');
$new_password     = get_post_val('password');

if (!$username || !$fullname || !$email) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
    exit;
}

// Determine ID column name in patient_auth
$id_field = 'patient_auth_id';
$checkCol = $conn->query("SHOW COLUMNS FROM patient_auth LIKE 'patient_auth_id'");
if (!$checkCol || $checkCol->num_rows === 0) {
    $id_field = 'id';
}

try {
    // If updating password, verify current password first
    if (!empty($new_password)) {
        if (empty($current_password)) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is required to set a new password.']);
            exit;
        }

        // Retrieve current password hash
        $passStmt = $conn->prepare("SELECT password_hash FROM patient_auth WHERE {$id_field} = ?");
        if (!$passStmt) {
            $passStmt = $conn->prepare("SELECT password FROM patient_auth WHERE {$id_field} = ?");
        }
        $passStmt->bind_param("i", $account_id);
        $passStmt->execute();
        $res = $passStmt->get_result()->fetch_assoc();
        $passStmt->close();

        $hashKey = isset($res['password_hash']) ? 'password_hash' : 'password';

        if (!$res || !password_verify($current_password, $res[$hashKey])) {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect current password.']);
            exit;
        }

        $options = ["cost" => 12];
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, $options);

        // Update with new password
        $sql = "UPDATE patient_auth SET username = ?, fullname = ?, email = ?, password_hash = ? WHERE {$id_field} = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $username, $fullname, $email, $hashed_password, $account_id);
    } else {
        // Update details without changing password
        $sql = "UPDATE patient_auth SET username = ?, fullname = ?, email = ? WHERE {$id_field} = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $fullname, $email, $account_id);
    }

    if ($stmt->execute()) {
        // Update active session data
        $_SESSION['logged_account'] = $fullname;
        $_SESSION['logged_name'] = $fullname;
        $_SESSION['username'] = $username;

        // Log the activity
        $logMsg = "Patient account " . $username . " updated their settings.";
        $logStmt = $conn->prepare("INSERT INTO logs (log_message) VALUES (?)");
        if ($logStmt) {
            $logStmt->bind_param("s", $logMsg);
            $logStmt->execute();
            $logStmt->close();
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Account settings updated successfully.'
        ]);
    } else {
        throw new Exception("Execution failed: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>