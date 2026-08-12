<?php
session_start();
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";
// exit();
require 'connection.php';

if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['register'])){

    // 1. Personal Info
    $firstname = trim($_POST['fname'] ?? '');
    $lastname  = trim($_POST['lname'] ?? '');
    $middlename = trim($_POST['mname'] ?? '');
    $suffix     = trim($_POST['sname'] ?? '');
    $address    = trim($_POST['address'] ?? '');

    $rawAge = $_POST['age'] ?? '';
    $age    = (int) preg_replace('/\D/', '', $rawAge);

    $gender           = trim($_POST['gender'] ?? '');
    $birthDate        = trim($_POST['birthDate'] ?? '');
    $contactnum       = trim($_POST['contactnum'] ?? '');
    $emailContact     = trim($_POST['emailContact'] ?? '');
    $employmentStatus = trim($_POST['employment_status'] ?? $_POST['employmentStatus'] ?? '');
    $occupation       = trim($_POST['occupation'] ?? '');
    $business         = trim($_POST['business'] ?? '');
    $civilStatus      = trim($_POST['civilStatus'] ?? '');
    $religion         = trim($_POST['religion'] ?? '');
    
    // 2. Family Info
    $mfname = trim($_POST['mfname'] ?? '');
    $mmname = trim($_POST['mmname'] ?? '');
    $mlname = trim($_POST['mlname'] ?? '');
    $mFullName = trim("$mfname $mmname $mlname");

    $ffname = trim($_POST['ffname'] ?? '');
    $fmname = trim($_POST['fmname'] ?? '');
    $flname = trim($_POST['flname'] ?? '');
    $fFullName = trim("$ffname $fmname $flname");

    $sfname = trim($_POST['sfname'] ?? '');
    $smname = trim($_POST['smname'] ?? '');
    $slname = trim($_POST['slname'] ?? '');
    $sFullName = trim("$sfname $smname $slname");

    // 3. PhilHealth & NHTS
    $philStatus = trim($_POST['philStatusInput'] ?? $_POST['philStatus'] ?? '');
    $philNum    = trim($_POST['philNumber'] ?? $_POST['philNum'] ?? '');
    $prinMem    = trim($_POST['prinMember'] ?? $_POST['prinMem'] ?? '');
    $nhtsStatus = trim($_POST['nhtsInput'] ?? $_POST['nhtsStatus'] ?? '');
    $nhtsNum    = trim($_POST['nhtsNumber'] ?? $_POST['nhtsNum'] ?? '');

    // Validation Checks
    if ($firstname === '' || $lastname === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide First and Last Name') . '&type=error');
        exit();
    } 

    if ($sFullName === ''){
      $sFullName = NULL;
    }

    if ($suffix === ''){
      $suffix = NULL;
    }

    if ($address === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide the address') . '&type=error');
        exit();
    }

    if ($gender === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select the gender') . '&type=error');
        exit();
    }

    if ($contactnum === '' || strlen($contactnum) < 11){
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide a valid numeric contact number') . '&type=error');
        exit();
    }

    if($emailContact === ''){
      $emailContact = NULL;
    }

    if ($employmentStatus === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select an Employment status') . '&type=error');
        exit();
    } else if ($employmentStatus === 'employed' && $occupation === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide occupation') . '&type=error');
        exit();
    } else if ($employmentStatus === 'self-employed' && $business === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide business') . '&type=error');
        exit();
    } else {
      $occupation = NULL;
      $business = NULL;
    }

    if ($civilStatus === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select civil status') . '&type=error');
        exit();
    }

    if ($religion === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select Religion') . '&type=error');
        exit();
    }

    if ($mfname === '' || $mlname === '' || $ffname === '' || $flname === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide parent names') . '&type=error');
        exit();
    }

    if ($philStatus === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select PhilHealth Status') . '&type=error');
        exit();
    } else if ($philStatus === 'member' && $philNum === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide PhilHealth Number') . '&type=error');
        exit();
    } else if ($philStatus === 'dependent' && ($philNum === '' || $prinMem === '')) {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide PhilHealth Number and Principal Member') . '&type=error');
        exit();
    } else {
      $philNum = NULL;
      $prinMem = NULL;
    }

    if ($nhtsStatus === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select NHTS Status') . '&type=error');
        exit();
    } else if ($nhtsStatus === 'yes' && $nhtsNum === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide NHTS Number') . '&type=error');
        exit();
    } else {
      $nhtsNum = NULL;
    }

    // FIXED: Select 'patient_id' instead of 'id'
    $check = $conn->prepare('SELECT patient_id FROM patient_records WHERE firstname = ? AND lastname = ? AND middlename = ? AND suffix = ?');
    if (!$check) {
        die("Duplicate Check Error: " . $conn->error);
    }
    
    $check->bind_param('ssss', $firstname, $lastname, $middlename, $suffix);
    $check->execute();
    $res = $check->get_result();

    if ($res && $res->num_rows > 0) {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Patient Already Existing') . '&type=error');
        exit();
    }
    $check->close();

    // Insert Query
    $sql = "INSERT INTO patient_records (
        firstname, lastname, middlename, suffix, address, age, gender, 
        date_of_birth, contact_number, email, employment_status, occupation, 
        business_name, civil_status, religion, mothers_name, fathers_name, 
        spouses_name, phil_status, phil_number, principal_member, nhts_member, nhts_number
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $insertstmt = $conn->prepare($sql);

    if (!$insertstmt) {
        die("Database Prepare Failed: " . $conn->error);
    }
    
    // Type definition: 5 strings, 1 int ($age), 17 strings
    $insertstmt->bind_param("sssssisssssssssssssssss", 
        $firstname, $lastname, $middlename, $suffix, $address, 
        $age, 
        $gender, $birthDate, $contactnum, $emailContact, 
        $employmentStatus, $occupation, $business, $civilStatus, 
        $religion, $mFullName, $fFullName, $sFullName, 
        $philStatus, $philNum, $prinMem, $nhtsStatus, $nhtsNum
    );

    if ($insertstmt->execute()) {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Successfully Registered') . '&type=success');
        exit();
    } else {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Error registering patient: ' . $insertstmt->error) . '&type=error');
        exit();
    }

} else {
    header('Location: ../pages/register_page.php?msg=' . urlencode('Invalid Request Method') . '&type=error');
    exit();
}
?>