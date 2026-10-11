<?php
session_start();
header('Content-Type: application/json');

require_once '../php/connection.php';
require_once '../api/check-private.php';

authorizeRoles(['Admin']);

// Date range inputs (defaults to current month or all-time if blank)
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$currentDate = date('Y-m-d');

try {
    // --- 1. TOP TILES (Total Patient is overall, Waiting/InProgress/Completed are for CURRENT DAY) ---
    $totalPatients = $conn->query("SELECT COUNT(*) AS c FROM patient_records")->fetch_assoc()['c'] ?? 0;
    
    $waiting = $conn->query("SELECT COUNT(*) AS c FROM queue_information WHERE (status = 'Waiting' OR status = 'Waiting') AND DATE(created_at) = '$currentDate'")->fetch_assoc()['c'] ?? 0;
    
    $inProgress = $conn->query("SELECT COUNT(*) AS c FROM queue_information WHERE (status = 'In Progress' OR status = 'In Progress') AND DATE(created_at) = '$currentDate'")->fetch_assoc()['c'] ?? 0;
    
    $completed = $conn->query("SELECT COUNT(*) AS c FROM queue_information WHERE (status = 'Completed' OR status = 'Completed') AND DATE(created_at) = '$currentDate'")->fetch_assoc()['c'] ?? 0;

    // --- 2. REGISTRATION TREND (Line Graph) ---
    $regTrendQuery = $conn->query("
        SELECT DATE(date_registered) as reg_date, COUNT(*) as cnt 
        FROM patient_records 
        WHERE DATE(date_registered) BETWEEN '$dateFrom' AND '$dateTo'
        GROUP BY DATE(date_registered)
        ORDER BY reg_date ASC
    ");
    $regLabels = []; $regData = [];
    while($row = $regTrendQuery->fetch_assoc()) {
        $regLabels[] = $row['reg_date'];
        $regData[] = intval($row['cnt']);
    }

    // --- 3. QUEUE TREND (Line Graph) ---
    $queueTrendQuery = $conn->query("
        SELECT DATE(transaction_date) as t_date, COUNT(*) as cnt 
        FROM transactions 
        WHERE DATE(transaction_date) BETWEEN '$dateFrom' AND '$dateTo'
        GROUP BY DATE(transaction_date)
        ORDER BY t_date ASC
    ");
    $queueLabels = []; $queueData = [];
    while($row = $queueTrendQuery->fetch_assoc()) {
        $queueLabels[] = $row['t_date'];
        $queueData[] = intval($row['cnt']);
    }

    // --- 4. AGE DISTRIBUTION (Bar Graph) ---
    // Grouping ages: 0-12, 13-19, 20-35, 36-59, 60+
    $ageGroups = ['0-12' => 0, '13-19' => 0, '20-35' => 0, '36-59' => 0, '60+' => 0];
    $ageQuery = $conn->query("SELECT age FROM patient_records WHERE age IS NOT NULL");
    while($row = $ageQuery->fetch_assoc()) {
        $age = intval($row['age']);
        if ($age <= 12) $ageGroups['0-12']++;
        elseif ($age <= 19) $ageGroups['13-19']++;
        elseif ($age <= 35) $ageGroups['20-35']++;
        elseif ($age <= 59) $ageGroups['36-59']++;
        else $ageGroups['60+']++;
    }

    // --- 5. CIVIL STATUS (Pie Chart) ---
    $civilQuery = $conn->query("SELECT civil_status, COUNT(*) as cnt FROM patient_records GROUP BY civil_status");
    $civilData = [];
    while($row = $civilQuery->fetch_assoc()) {
        $status = $row['civil_status'] ?: 'Not Specified';
        $civilData[$status] = intval($row['cnt']);
    }

    // --- 6. GENDER DISTRIBUTION (Pie Chart) ---
    $genderQuery = $conn->query("SELECT gender, COUNT(*) as cnt FROM patient_records GROUP BY gender");
    $genderData = [];
    while($row = $genderQuery->fetch_assoc()) {
        $gender = $row['gender'] ?: 'Not Specified';
        $genderData[$gender] = intval($row['cnt']);
    }

    // --- 7. SERVICE DISTRIBUTION (Horizontal Bar Graph) ---
    $serviceQuery = $conn->query("SELECT service, COUNT(*) as cnt FROM transactions GROUP BY service");
    $serviceLabels = []; $serviceCounts = [];
    while($row = $serviceQuery->fetch_assoc()) {
        $serviceLabels[] = $row['service'] ?: 'General';
        $serviceCounts[] = intval($row['cnt']);
    }

    // --- 8. EMPLOYMENT STATUS (Pie Chart: Employed, Self-Employed, Unemployed) ---
    $empQuery = $conn->query("SELECT employment_status, COUNT(*) as cnt FROM patient_records GROUP BY employment_status");
    $empData = ['Employed' => 0, 'Self-Employed' => 0, 'Unemployed' => 0];
    while($row = $empQuery->fetch_assoc()) {
        $status = ucfirst(strtolower(trim($row['employment_status'] ?? '')));
        if (array_key_exists($status, $empData)) {
            $empData[$status] = intval($row['cnt']);
        } else {
            $empData['Unemployed'] += intval($row['cnt']);
        }
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
            'registration_trend' => ['labels' => $regLabels, 'data' => $regData],
            'queue_trend' => ['labels' => $queueLabels, 'data' => $queueData],
            'age_distribution' => ['labels' => array_keys($ageGroups), 'data' => array_values($ageGroups)],
            'civil_status' => ['labels' => array_keys($civilData), 'data' => array_values($civilData)],
            'gender' => ['labels' => array_keys($genderData), 'data' => array_values($genderData)],
            'service' => ['labels' => $serviceLabels, 'data' => $serviceCounts],
            'employment' => ['labels' => array_keys($empData), 'data' => array_values($empData)]
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
$conn->close();
?>