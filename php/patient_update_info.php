<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
require_once '../api/check-private.php';
date_default_timezone_set('Asia/Manila');

// Ensure only logged-in patients can update their own info
authorizeRoles(['Patient']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit();
}

// Obtain patient ID from session
$patient_id = $_SESSION['patient_id'] ?? $_SESSION['logged_id'] ?? 0;

if ($patient_id <= 0) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid session or missing patient ID.'
    ]);
    exit();
}

function get_post_val($key, $default = null) {
    if (isset($_POST[$key]) && trim($_POST[$key]) !== '') {
        return trim($_POST[$key]);
    }
    return $default;
}

$fname              = get_post_val('editFirstName');
$mname              = get_post_val('editMiddleName');
$lname              = get_post_val('editLastName');
$sname              = get_post_val('editSuffix');
$gender             = get_post_val('editGender');
$birthdate          = get_post_val('editBirthDate');
$civil_status       = get_post_val('editCivilStatus');
$religion           = get_post_val('editReligion');

$contactnum         = get_post_val('editContact');
$email              = get_post_val('editEmail');
$address            = get_post_val('editAddress');

$mother_maiden_name = get_post_val('editMotherName');
$father_name        = get_post_val('editFatherName');
$spouse_name        = get_post_val('editSpouseName');

$employment_status  = get_post_val('editEmploymentStatus');
$occupation         = get_post_val('editOccupation');
$business           = get_post_val('editBusiness');

$phil_status        = get_post_val('editPhilStatus');
$phil_number        = get_post_val('editPhilNumber');
$prin_member        = get_post_val('editPrinMember');
$yakap_member       = get_post_val('editYakapMember');

if (!$fname || !$lname || !$gender || !$birthdate) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'First Name, Last Name, Gender, and Birth Date are required.'
    ]);
    exit();
}


$address_id = 0;

if ($address === '') {
    header('Location: ../pages/patient_info_page.php?msg=' . urlencode('Please provide the address') . '&type=error');
    exit();
} else {
    // Ensure the table and column names match your database schema
    $barangayCheck = $conn->prepare("SELECT barangay_id FROM barangay WHERE barangay_address = ? LIMIT 1");
    $barangayCheck->bind_param("s", $address);
    $barangayCheck->execute();
    $barangayRes = $barangayCheck->get_result();
    
    if ($barangayRes->num_rows === 0) {
        $barangayCheck->close();
        header('Location: ../pages/patient_info_page.php?msg=' . urlencode('Please select a valid barangay from the list.') . '&type=error');
        exit();
    } else {
        // FIX: Fetch the row into an array first
        $row = $barangayRes->fetch_assoc();
        $address_id = $row['barangay_id'];
    }
    $barangayCheck->close();
}



if ($sname === 'None') {
    $sname = NULL;
}

try {
    $conn->begin_transaction();

    // 1. Update patient_records table
    $sql = "UPDATE patient_records SET 
                firstname = ?, 
                middlename = ?, 
                lastname = ?, 
                suffix = ?,
                gender = ?, 
                date_of_birth = ?, 
                civil_status = ?, 
                religion = ?, 
                contact_number = ?, 
                email = ?,
                address = ?, 
                mothers_name = ?, 
                fathers_name = ?, 
                spouses_name = ?, 
                employment_status = ?, 
                occupation = ?, 
                business_name = ?, 
                phil_status = ?, 
                phil_number = ?, 
                principal_member = ?, 
                yakap_member = ?
            WHERE patient_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }

    $stmt->bind_param(
        "ssssssssssissssssssssi",
        $fname,
        $mname,
        $lname,
        $sname,  
        $gender,
        $birthdate,
        $civil_status,
        $religion,
        $contactnum,
        $email,
        $address_id,
        $mother_maiden_name,
        $father_name,
        $spouse_name,
        $employment_status,
        $occupation,
        $business,
        $phil_status,
        $phil_number,
        $prin_member,
        $yakap_member,
        $patient_id
    );

    if (!$stmt->execute()) {
        throw new Exception("Execution failed: " . $stmt->error);
    }
    $stmt->close();

    // 2. Optionally update email in patient_auth table if provided
    if ($email) {
        $stmtAuth = $conn->prepare("UPDATE patient_auth SET email = ? WHERE patient_id = ?");
        if ($stmtAuth) {
            $stmtAuth->bind_param("ii", $patient_id, $patient_id);
            $stmtAuth->execute();
            $stmtAuth->close();
        }
    }

    $conn->commit();

    echo json_encode([
        'status'  => 'success',
        'message' => 'Your information has been updated successfully.'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>