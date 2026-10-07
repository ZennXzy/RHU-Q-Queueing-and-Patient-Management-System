<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';

$type = isset($_GET['type']) && $_GET['type'] === 'patient' ? 'patient' : 'staff';
$excluded = $_SESSION['logged_id'] ?? 0;

if ($type === 'staff') {
    $table = "admin_auth";
    $query = "SELECT id, username, fullname, email, status, role, created_at, last_active FROM admin_auth WHERE id != ?";
    $totalQuery = "SELECT COUNT(*) AS total FROM admin_auth";
    $activeQuery = "SELECT COUNT(*) AS active FROM admin_auth WHERE status = 'Online' AND id != ?";
} else {
    $table = "patient_auth";
    $query = "SELECT patient_auth_id, username, fullname, email, account_token, status, 'Patient' AS role, created_at, last_active FROM patient_auth";
    $totalQuery = "SELECT COUNT(*) AS total FROM patient_auth";
    $activeQuery = "SELECT COUNT(*) AS active FROM patient_auth WHERE status = 'Online'";
}

$totalRes = $conn->query($totalQuery);
$totalRow = $totalRes ? $totalRes->fetch_assoc() : ['total' => 0];

if ($type === 'staff') {
    $activeStmt = $conn->prepare($activeQuery);
    $activeStmt->bind_param("i", $excluded);
    $activeStmt->execute();
    $activeRow = $activeStmt->get_result()->fetch_assoc();
    $activeStmt->close();
} else {
    $activeRes = $conn->query($activeQuery);
    $activeRow = $activeRes ? $activeRes->fetch_assoc() : ['active' => 0];
}

if ($type === 'staff') {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $excluded);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($query);
}

ob_start();
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $statusClass = (strtolower($row['status']) === 'online') ? 'badge-online' : 'badge-offline';
        echo '<tr>';
        echo '<td style="text-align: center;">#</td>';
        echo '<td>' . htmlspecialchars($row['username']) . '</td>';
        echo '<td>' . htmlspecialchars($row['fullname']) . '</td>';
        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
        echo '<td style="text-align: center;"><span class="badge ' . $statusClass . '">' . htmlspecialchars(ucfirst($row['status'])) . '</span></td>';
        if ($type === 'staff'){
            echo '<td style="text-align: center;">' . htmlspecialchars($row['role']) . '</td>';
        } else {
            echo '<td style="text-align: center;">' . htmlspecialchars($row['account_token']) . '</td>';
        }
        echo '<td style="text-align: center;">' . htmlspecialchars($row['created_at']) . '</td>';
        echo '<td style="text-align: center;">' . htmlspecialchars($row['last_active']) . '</td>';
        echo '<td>';
        echo '<div class="table-actions">';
        if ($type === 'staff'){
            echo '<button class="edit-btn" data-id="' . htmlspecialchars($row['id']) . '" data-type="' . $type . '"><img src="../svg/edit-icon.svg" alt="edit-icon"></button>';
            echo '<button class="delete-btn" data-id="' . htmlspecialchars($row['id']) . '" data-type="' . $type . '"><img src="../svg/delete-icon.svg" alt="delete-icon"></button>';
        } else {
            echo '<button class="edit-btn" data-id="' . htmlspecialchars($row['patient_auth_id']) . '" data-type="' . $type . '"><img src="../svg/edit-icon.svg" alt="edit-icon"></button>';
            echo '<button class="delete-btn" data-id="' . htmlspecialchars($row['patient_auth_id']) . '" data-type="' . $type . '"><img src="../svg/delete-icon.svg" alt="delete-icon"></button>';
        }
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="9" style="text-align: center; padding: 20px;">No accounts found.</td></tr>';
}
$html = ob_get_clean();


echo json_encode([
    'status' => 'success',
    'html' => $html,
    'total' => $totalRow['total'] ?? 0,
    'active' => $activeRow['active'] ?? 0
]);
?>