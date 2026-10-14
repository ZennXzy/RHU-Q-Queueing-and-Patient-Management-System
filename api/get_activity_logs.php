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

    // Initialize variables properly
    $whereSql = "";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $whereSql = " WHERE log_message LIKE ? OR log_date LIKE ? ";
        $searchTerm = "%" . $search . "%";
        $params = [$searchTerm, $searchTerm];
        $types = "ss";
    }

    // 1. Get Total Record Count for Pagination
    $countSql = "SELECT COUNT(*) AS total FROM logs" . $whereSql;
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

    // 2. Fetch Paginated Records
    $sql = "SELECT log_message, log_date FROM logs" . $whereSql . " ORDER BY log_date DESC LIMIT ? OFFSET ?";
    
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
            $row['log_message'],
            $row['log_date']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'columns' => ['Activity Message', 'Date & Time'],
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