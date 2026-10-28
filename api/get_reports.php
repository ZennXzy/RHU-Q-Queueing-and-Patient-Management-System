<?php
session_start();
header('Content-Type: application/json');

require_once '../php/connection.php';
require_once '../api/check-private.php';

// Ensure user is authorized for admin/reports
authorizeRoles(['Admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Handle single record detail view request
if (isset($_GET['action']) && $_GET['action'] === 'details' && isset($_GET['id'])) {
    $record_id = intval($_GET['id']);
    try {
        $detailQuery = "
            SELECT 
                t.*,
                TRIM(CONCAT(
                    COALESCE(p.lastname, ''), ', ',
                    COALESCE(p.firstname, ''), ' ', 
                    COALESCE(p.middlename, ''), '.'
                )) AS fullname,
                TRIM(CONCAT(
                    CONCAT(COALESCE(NULLIF(t.blood_pressure, ''), 'N/A'), ' (mmHg)')
                )) AS blood_pressure,
                TRIM(CONCAT(
                    CONCAT(COALESCE(NULLIF(t.respiratory_rate, ''), 'N/A'), ' (bpm)')
                )) AS respiratory_rate,
                TRIM(CONCAT(
                    CONCAT(COALESCE(NULLIF(t.temperature, ''), 'N/A'), ' (°C)')
                )) AS temperature,
                TRIM(CONCAT(
                    CONCAT(COALESCE(NULLIF(t.pulse_rate, ''), 'N/A'), ' (bpm)')
                )) AS pulse_rate,
                TRIM(CONCAT(
                    CONCAT(COALESCE(NULLIF(t.weight, ''), 'N/A'), ' (kg)')
                )) AS weight,
                TRIM(CONCAT(
                    CONCAT(COALESCE(NULLIF(t.height, ''), 'N/A'), ' (cm)')
                )) AS height,
                p.gender,
                b.barangay_address AS address,
                s.service_name AS service_type,
                p.age
            FROM transactions t
            LEFT JOIN patient_records p ON t.patient_id = p.patient_id
            LEFT JOIN barangay b ON p.address = b.barangay_id
            LEFT JOIN services s ON t.service = s.service_id
            WHERE t.transaction_id = ?
        ";
        $stmt = $conn->prepare($detailQuery);
        $stmt->bind_param("i", $record_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($res) {
            echo json_encode(['status' => 'success', 'record' => $res]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Record not found.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

try {
    // Base dynamic query joining transactions with patient profiles
    $where = ["1=1"];
    $params = [];
    $types = "";

    // Search query (Concatenated Name or Queue Code)
    if (!empty($_GET['search'])) {
        $searchTerm = "%" . trim($_GET['search']) . "%";
        $where[] = "(CONCAT(COALESCE(p.firstname, ''), ' ', COALESCE(p.lastname, '')) LIKE ? OR t.queue_code LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }

    // Date Range Filter (Flexible: allows from-only, to-only, or both)
    if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
        $where[] = "DATE(t.transaction_date) BETWEEN ? AND ?";
        $params[] = $_GET['date_from'];
        $params[] = $_GET['date_to'];
        $types .= "ss";
    } elseif (!empty($_GET['date_from'])) {
        $where[] = "DATE(t.transaction_date) >= ?";
        $params[] = $_GET['date_from'];
        $types .= "s";
    } elseif (!empty($_GET['date_to'])) {
        $where[] = "DATE(t.transaction_date) <= ?";
        $params[] = $_GET['date_to'];
        $types .= "s";
    }

    // Age Range Filter
    if (isset($_GET['age_min']) && $_GET['age_min'] !== '') {
        $where[] = "p.age >= ?";
        $params[] = intval($_GET['age_min']);
        $types .= "i";
    }
    if (isset($_GET['age_max']) && $_GET['age_max'] !== '') {
        $where[] = "p.age <= ?";
        $params[] = intval($_GET['age_max']);
        $types .= "i";
    }

    // Service Taken Filter
    if (!empty($_GET['service'])) {
        $where[] = "t.service = ?";
        $params[] = $_GET['service'];
        $types .= "s";
    }

    // Gender Filter
    if (!empty($_GET['gender'])) {
        $where[] = "p.gender = ?";
        $params[] = $_GET['gender'];
        $types .= "s";
    }

    // Address Filter
    if (!empty($_GET['address'])) {
        $where[] = "p.address LIKE ?";
        $params[] = "%" . trim($_GET['address']) . "%";
        $types .= "s";
    }

    $query = "
        SELECT 
            t.transaction_id,
            t.queue_code,
            s.service_name AS service_type,
            COALESCE(t.blood_pressure, 'N/A') AS blood_pressure,
            COALESCE(t.respiratory_rate, 'N/A') AS respiratory_rate,
            COALESCE(t.temperature, 'N/A') AS temperature,
            COALESCE(t.pulse_rate, 'N/A') AS pulse_rate,
            COALESCE(t.weight, 'N/A') AS weight,
            COALESCE(t.height, 'N/A') AS height,
            COALESCE(t.diagnosis, 'None Recorded') AS diagnosis,
            DATE_FORMAT(t.transaction_date, '%b %d, %Y %h:%i %p') AS formatted_date,
            TRIM(CONCAT(
                COALESCE(p.lastname, ''), ', ',
                COALESCE(p.firstname, ''), ' ', 
                COALESCE(p.middlename, ''), '.'
            )) AS fullname,
            COALESCE(p.age, 'N/A') AS age,
            COALESCE(p.gender, 'N/A') AS gender,
            COALESCE(b.barangay_address, 'N/A') AS address
        FROM transactions t
        LEFT JOIN patient_records p ON t.patient_id = p.patient_id
        LEFT JOIN services s ON t.service = s.service_id
        LEFT JOIN barangay b ON p.address = b.barangay_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY t.transaction_date DESC
    ";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $reports = [];
    $htmlRows = "";
    $counter = 1;

    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
        
        // Build table row HTML for immediate frontend injection
        $htmlRows .= "<tr>
            <td style='text-align: center;'>{$counter}</td>
            <td>{$row['formatted_date']}</td>
            <td><strong>" . htmlspecialchars($row['fullname']) . "</strong><br><small style='color:#6b7280;'>Queue: {$row['queue_code']}</small></td>
            <td style='text-align: center;'>" . htmlspecialchars($row['age']) . " / " . htmlspecialchars($row['gender']) . "</td>
            <td>" . htmlspecialchars($row['service_type']) . "</td>
            <td>" . htmlspecialchars($row['address']) . "</td>
            <td style='text-align: center;'>
                <button class='view-record-btn' data-id='{$row['transaction_id']}' title='View Details'>👁️</button>
            </td>
        </tr>";
        $counter++;
    }
    $stmt->close();

    if (empty($reports)) {
        $htmlRows = "<tr><td colspan='7' style='text-align: center; padding: 20px; color: #6b7280;'>No records found matching your filters.</td></tr>";
    }

    echo json_encode([
        'status' => 'success',
        'total' => count($reports),
        'data' => $reports,
        'html' => $htmlRows
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>