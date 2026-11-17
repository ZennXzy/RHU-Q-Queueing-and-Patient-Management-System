<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once '../php/connection.php';
require_once '../api/check-private.php';

date_default_timezone_set('Asia/Manila');

authorizeRoles(['Admin', 'Doctor/Staff']);

try {
    $dateFrom = trim($_GET['date_from'] ?? '');
    $dateTo = trim($_GET['date_to'] ?? '');

    function getCount($conn, $sql, $params = [], $types = '') {
        if ($params) {
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception($conn->error);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }

        if (!$result) {
            throw new Exception($conn->error);
        }

        return (int)($result->fetch_assoc()['c'] ?? 0);
    }

    function getDistribution($conn, $column, $dateFrom, $dateTo) {
        $allowed = [
            'civil_status',
            'gender',
            'employment_status',
            'phil_status',
            'yakap_member'
        ];

        if (!in_array($column, $allowed, true)) {
            throw new Exception('Invalid distribution column.');
        }

        if ($dateFrom !== '' && $dateTo !== '') {
            $sql = "
                SELECT
                    COALESCE(
                        NULLIF(TRIM(p.`$column`), ''),
                        'Not Specified'
                    ) AS label,
                    COUNT(DISTINCT p.patient_id) AS cnt
                FROM patient_records p
                JOIN transactions t ON t.patient_id = p.patient_id
                WHERE t.transaction_date >= ? AND t.transaction_date < DATE_ADD(?, INTERVAL 1 DAY)
                GROUP BY label
                ORDER BY cnt DESC, label ASC
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception($conn->error);
            $fromDateStr = $dateFrom . ' 00:00:00';
            $stmt->bind_param("ss", $fromDateStr, $dateTo);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "
                SELECT
                    COALESCE(
                        NULLIF(TRIM(`$column`), ''),
                        'Not Specified'
                    ) AS label,
                    COUNT(*) AS cnt
                FROM patient_records
                GROUP BY label
                ORDER BY cnt DESC, label ASC
            ";
            $result = $conn->query($sql);
        }

        if (!$result) {
            throw new Exception($conn->error);
        }

        $distribution = [];
        while ($row = $result->fetch_assoc()) {
            $distribution[$row['label']] = (int)$row['cnt'];
        }

        return [
            'labels' => array_keys($distribution),
            'data' => array_values($distribution)
        ];
    }

    // Total Patients: if filtered, count unique patients with transactions in range, else all registered patients
    if ($dateFrom !== '' && $dateTo !== '') {
        $totalPatients = getCount(
            $conn,
            "SELECT COUNT(DISTINCT t.patient_id) AS c FROM transactions t WHERE t.transaction_date >= ? AND t.transaction_date < DATE_ADD(?, INTERVAL 1 DAY)",
            [$dateFrom . ' 00:00:00', $dateTo],
            'ss'
        );
    } else {
        $totalPatients = getCount($conn, "SELECT COUNT(*) AS c FROM patient_records");
    }

    $waiting = getCount(
        $conn,
        "SELECT COUNT(*) AS c
         FROM queue_information
         WHERE status = 'Waiting'
         AND created_at >= CURDATE()
         AND created_at < CURDATE() + INTERVAL 1 DAY"
    );

    $inProgress = getCount(
        $conn,
        "SELECT COUNT(*) AS c
         FROM queue_information
         WHERE status = 'In Progress'
         AND created_at >= CURDATE()
         AND created_at < CURDATE() + INTERVAL 1 DAY"
    );

    if ($dateFrom !== '' && $dateTo !== '') {
        $completed = getCount(
            $conn,
            "SELECT COUNT(*) AS c
             FROM queue_information
             WHERE status = 'Completed'
             AND DATE(created_at) >= ?
             AND DATE(created_at) <= ?",
            [$dateFrom, $dateTo],
            'ss'
        );
    } else {
        $completed = getCount(
            $conn,
            "SELECT COUNT(*) AS c
             FROM queue_information
             WHERE status = 'Completed'
             AND created_at >= CURDATE()
             AND created_at < CURDATE() + INTERVAL 1 DAY"
        );
    }

    $ageGroups = [
        '0-12' => 0,
        '13-19' => 0,
        '20-35' => 0,
        '36-59' => 0,
        '60+' => 0,
        'Not Specified' => 0
    ];

    if ($dateFrom !== '' && $dateTo !== '') {
        $ageSql = "SELECT DISTINCT p.patient_id, TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age FROM patient_records p JOIN transactions t ON t.patient_id = p.patient_id WHERE t.transaction_date >= ? AND t.transaction_date < DATE_ADD(?, INTERVAL 1 DAY)";
        $stmt = $conn->prepare($ageSql);
        $fromDateStr = $dateFrom . ' 00:00:00';
        $stmt->bind_param("ss", $fromDateStr, $dateTo);
        $stmt->execute();
        $ageQuery = $stmt->get_result();
    } else {
        $ageQuery = $conn->query("SELECT TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) AS age FROM patient_records");
    }

    if (!$ageQuery) {
        throw new Exception($conn->error);
    }

    while ($row = $ageQuery->fetch_assoc()) {
        if (
            $row['age'] === null ||
            $row['age'] === '' ||
            !is_numeric($row['age']) ||
            (int)$row['age'] < 0
        ) {
            $ageGroups['Not Specified']++;
            continue;
        }

        $age = (int)$row['age'];

        if ($age <= 12) {
            $ageGroups['0-12']++;
        } elseif ($age <= 19) {
            $ageGroups['13-19']++;
        } elseif ($age <= 35) {
            $ageGroups['20-35']++;
        } elseif ($age <= 59) {
            $ageGroups['36-59']++;
        } else {
            $ageGroups['60+']++;
        }
    }

    if ($ageGroups['Not Specified'] === 0) {
        unset($ageGroups['Not Specified']);
    }

    $civilData = getDistribution($conn, 'civil_status', $dateFrom, $dateTo);
    $genderData = getDistribution($conn, 'gender', $dateFrom, $dateTo);
    $employmentData = getDistribution($conn, 'employment_status', $dateFrom, $dateTo);
    $philhealthData = getDistribution($conn, 'phil_status', $dateFrom, $dateTo);
    $yakapData = getDistribution($conn, 'yakap_member', $dateFrom, $dateTo);

    // Service Distribution
    $serviceWhere = "WHERE 1=1";
    $serviceParams = [];
    $serviceTypes = "";

    if ($dateFrom !== '') {
        $serviceWhere .= " AND t.transaction_date >= ?";
        $serviceParams[] = $dateFrom . ' 00:00:00';
        $serviceTypes .= 's';
    }
    if ($dateTo !== '') {
        $serviceWhere .= " AND t.transaction_date < DATE_ADD(?, INTERVAL 1 DAY)";
        $serviceParams[] = $dateTo;
        $serviceTypes .= 's';
    }

    $serviceSQL = "
        SELECT
            COALESCE(
                NULLIF(TRIM(t.service_snapshot), ''),
                'Unknown Service'
            ) AS service_label,
            COUNT(*) AS cnt
        FROM transactions AS t
        $serviceWhere
        GROUP BY service_label
        ORDER BY cnt DESC, service_label ASC
    ";

    $stmt = $conn->prepare($serviceSQL);
    if (!$stmt) throw new Exception($conn->error);
    if ($serviceParams) {
        $stmt->bind_param($serviceTypes, ...$serviceParams);
    }
    $stmt->execute();
    $serviceQuery = $stmt->get_result();

    if (!$serviceQuery) {
        throw new Exception($conn->error);
    }

    $serviceLabels = [];
    $serviceCounts = [];

    while ($row = $serviceQuery->fetch_assoc()) {
        $serviceLabels[] = $row['service_label'];
        $serviceCounts[] = (int)$row['cnt'];
    }

    echo json_encode([
        'status' => 'success',
        'tiles' => [
            'total_patients' => $totalPatients,
            'waiting' => $waiting,
            'in_progress' => $inProgress,
            'completed' => $completed
        ],
        'charts' => [
            'age_distribution' => [
                'labels' => array_keys($ageGroups),
                'data' => array_values($ageGroups)
            ],
            'civil_status' => $civilData,
            'gender' => $genderData,
            'employment' => $employmentData,
            'philhealth' => $philhealthData,
            'yakap' => $yakapData,
            'service' => [
                'labels' => $serviceLabels,
                'data' => $serviceCounts
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    error_log('Dashboard API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to load dashboard statistics: ' . $e->getMessage()
    ]);
}
$conn->close();
?>