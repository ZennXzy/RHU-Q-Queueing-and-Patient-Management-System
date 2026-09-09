<?php
header('Content-Type: application/json');
require_once '../php/connection.php'; 
require_once '../php/check-private.php';
if (!canManageRecords()) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Unauthorized action. Admin privileges required.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;

if ($patient_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing Patient ID.'
    ]);
    exit;
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

if (!$fname || !$lname || !$gender || !$birthdate) {
    header('Location: ../pages/patient_records.php?msg=' . urlencode('Invalid credentials') . '&type=error');
    exit();
}

if(get_post_val('editSuffix') === 'None'){
    $sname = NULL;
}

try {
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

    $stmt->bind_param(
        "sssssssssssssssssssssi",
        $fname,
        $mname,
        $lname,
        $sname,  
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