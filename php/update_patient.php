<?php
// php/update_patient.php

// Set response header to JSON
header('Content-Type: application/json');

// Include your database connection script
// Adjust path if your database file is located elsewhere (e.g., require_once 'db_connect.php';)
require_once '../php/connection.php'; 

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Retrieve and sanitize hidden Patient ID
$patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;

if ($patient_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing Patient ID.'
    ]);
    exit;
}

// Helper function to safely get and sanitize POST parameters
function get_post_val($key, $default = null) {
    if (isset($_POST[$key]) && trim($_POST[$key]) !== '') {
        return trim($_POST[$key]);
    }
    return $default;
}

// Capture and sanitize form inputs matching form field 'name' attributes
$fname              = get_post_val('editFirstName');
$mname              = get_post_val('editMiddleName');
$lname              = get_post_val('editLastName');
$sname              = get_post_val('editSuffix');
$gender             = get_post_val('editGender');
$birthdate          = get_post_val('editBirthDate');
$civil_status       = get_post_val('editCivilStatus');
$religion           = get_post_val('editReligion');

$contactnum         = get_post_val('editContact');
$address            = get_post_val('editAddress');
$date_registered    = get_post_val('editDateRegistered');


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

// Mandatory fields validation
if (!$fname || !$lname || !$gender || !$birthdate) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please fill in all required fields (First Name, Last Name, Gender, Birth Date).'
    ]);
    exit;
}

try {
    // SQL Query (Update column names to match your MySQL database schema)
    $sql = "UPDATE patient_records SET 
                firstname = ?, 
                middlename = ?, 
                lastname = ?, 
                gender = ?, 
                date_of_birth = ?, 
                civil_status = ?, 
                religion = ?, 
                contact_number = ?, 
                address = ?, 
                date_registered = ?, 
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

    // Bind values (20 strings/null values + 1 integer ID = 21 parameters)
    $stmt->bind_param(
        "ssssssssssssssssssssi",
        $fname,
        $mname,
        $lname,
        $gender,
        $birthdate,
        $civil_status,
        $religion,
        $contactnum,
        $address,
        $date_registered,
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

    // Execute statement
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Patient profile updated successfully.'
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