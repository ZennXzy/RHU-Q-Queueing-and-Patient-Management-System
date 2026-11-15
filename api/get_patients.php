<?php
header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? '';

// --- 1. DETAILS ACTION ---
if ($action === 'details') {
    $patient_id = $_GET['id'] ?? null;

    if ($patient_id === 'session' || empty($patient_id)) {
        $patient_id = $_SESSION['patient_id'] ?? $_SESSION['user_id'] ?? null;
    }

    if (!$patient_id) {
        echo json_encode(['status' => 'error', 'message' => 'No patient ID provided']);
        exit;
    }

    $stmt = $conn->prepare("SELECT p.*,
                            TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
                            p.barangay_id AS barangay_id,
                            TRIM(b.barangay_name) AS address
                            FROM patient_records p
                            JOIN barangay b ON p.barangay_id = b.barangay_id
                            WHERE p.patient_id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($patient = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'patient' => $patient]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Patient record not found']);
    }
    exit;
}

// --- 2. FILTER & FULL EXPORT ACTION ---
if ($action === 'filter') {
    $gender            = trim($_GET['gender'] ?? '');
    $address           = trim($_GET['address'] ?? '');
    $age_min           = isset($_GET['age_min']) && $_GET['age_min'] !== '' ? intval($_GET['age_min']) : null;
    $age_max           = isset($_GET['age_max']) && $_GET['age_max'] !== '' ? intval($_GET['age_max']) : null;
    $employment_status = trim($_GET['employment_status'] ?? '');
    $civil_status      = trim($_GET['civil_status'] ?? '');
    $religion          = trim($_GET['religion'] ?? '');
    $phil_status       = trim($_GET['phil_status'] ?? '');
    $yakap_member      = trim($_GET['yakap_member'] ?? '');
    $date_from         = trim($_GET['date_from'] ?? '');
    $date_to           = trim($_GET['date_to'] ?? '');

    // Dynamically calculate age in SQL query
    $sql = "SELECT p.*, 
                   TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
                   p.barangay_id AS barangay_id,
                   TRIM(b.barangay_name) AS address
            FROM patient_records p
            LEFT JOIN barangay b ON p.barangay_id = b.barangay_id
            WHERE 1=1";

    $params = [];
    $types = "";

    if ($gender !== '') {
        $sql .= " AND p.gender = ?";
        $params[] = $gender;
        $types .= "s";
    }

    if ($address !== '') {
        $sql .= " AND b.barangay_name LIKE ?";
        $params[] = "%" . $address . "%";
        $types .= "s";
    }

    if ($age_min !== null) {
        $sql .= " AND TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) >= ?";
        $params[] = $age_min;
        $types .= "i";
    }

    if ($age_max !== null) {
        $sql .= " AND TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) <= ?";
        $params[] = $age_max;
        $types .= "i";
    }

    if ($employment_status !== '') {
        $sql .= " AND p.employment_status = ?";
        $params[] = $employment_status;
        $types .= "s";
    }

    if ($civil_status !== '') {
        $sql .= " AND p.civil_status = ?";
        $params[] = $civil_status;
        $types .= "s";
    }

    if ($religion !== '') {
        $sql .= " AND p.religion = ?";
        $params[] = $religion;
        $types .= "s";
    }

    if ($phil_status !== '') {
        $sql .= " AND p.phil_status = ?";
        $params[] = $phil_status;
        $types .= "s";
    }

    if ($yakap_member !== '') {
        $sql .= " AND p.yakap_member = ?";
        $params[] = $yakap_member;
        $types .= "s";
    }

    if ($date_from !== '') {
        $sql .= " AND p.date_registered >= ?";
        $params[] = $date_from;
        $types .= "s";
    }

    if ($date_to !== '') {
        $sql .= " AND p.date_registered <= ?";
        $params[] = $date_to;
        $types .= "s";
    }

    $sql .= " ORDER BY p.firstname ASC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $patients = [];
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }

    echo json_encode(['status' => 'success', 'patients' => $patients]);
    exit;
}
?>