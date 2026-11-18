<?php
require_once '../api/check-private.php';
authorizeRoles(['Admin']);
require_once '../php/connection.php';

header('Content-Type: application/json');

try {
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $whereSql = "";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $whereSql = " WHERE l.log_message LIKE ? OR l.log_date LIKE ? OR l.user_type LIKE ? ";
        $searchTerm = "%" . $search . "%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
        $types = "sss";
    }

    $countSql = "SELECT COUNT(*) AS total FROM logs l" . $whereSql;
    $stmtCount = $conn->prepare($countSql);
    
    if ($stmtCount === false) {
        throw new Exception("Prepare failed (count): " . $conn->error);
    }

    if (!empty($params)) {
        $stmtCount->bind_param($types, ...$params);
    }
    
    $stmtCount->execute();
    $totalRecords = $stmtCount->get_result()->fetch_assoc()['total'] ?? 0;
    $totalPages = max(1, ceil($totalRecords / $limit));

    $sql = "
        SELECT 
            l.log_message, 
            l.user_type, 
            l.log_date,
            CASE 
                WHEN l.user_type = 'Patient' THEN COALESCE(CONCAT_WS(' ', pr.firstname, pr.lastname), p.username, CONCAT('Patient #', l.user_id))
                ELSE COALESCE(a.fullname, a.username, CONCAT('User #', l.user_id))
            END AS user_name
        FROM logs l
        LEFT JOIN admin_auth a ON l.user_id = a.id AND l.user_type != 'Patient'
        LEFT JOIN patient_auth p ON l.user_id = p.patient_auth_id AND l.user_type = 'Patient'
        LEFT JOIN patient_records pr ON p.patient_id = pr.patient_id
        " . $whereSql . " 
        ORDER BY l.log_date DESC 
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Prepare failed (select): " . $conn->error);
    }

    if (!empty($params)) {
        $stmtTypes = $types . "ii";
        $stmtParams = array_merge($params, [$limit, $offset]);
        $stmt->bind_param($stmtTypes, ...$stmtParams);
    } else {
        $stmt->bind_param("ii", $limit, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = [
            $row['user_name'] ?? 'System',
            $row['user_type'] ?? 'System',
            $row['log_message'],
            $row['log_date']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'columns' => ['User', 'User Type', 'Activity Message', 'Date & Time'],
        'logs' => $logs,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_records' => $totalRecords
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load logs: ' . $e->getMessage()
    ]);
}
?>