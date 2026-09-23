<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';


if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['register'])){

    // Save submitted data to session so fields persist if an error occurs
    $_SESSION['form_data'] = $_POST;

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
    $employmentStatus = trim($_POST['employment_status'] ?? '');
    $occupation       = trim($_POST['occupation'] ?? '');
    $business         = trim($_POST['business'] ?? '');
    $civilStatus      = trim($_POST['civilStatus'] ?? '');
    $religion         = trim($_POST['religion'] ?? '');
    
    // 2. Family Info
    $mfname = trim($_POST['mfname'] ?? '');
    $mmname = trim($_POST['mmname'] ?? '');
    $mlname = trim($_POST['mlname'] ?? '');
    $mFullName = trim("$mlname $mfname $mmname");

    $ffname = trim($_POST['ffname'] ?? '');
    $fmname = trim($_POST['fmname'] ?? '');
    $flname = trim($_POST['flname'] ?? '');
    $fsname = trim($_POST['fsname'] ?? '');
    $fFullName = trim("$flname $ffname $fmname $fsname");

    $sfname = trim($_POST['sfname'] ?? '');
    $smname = trim($_POST['smname'] ?? '');
    $slname = trim($_POST['slname'] ?? '');
    $ssname = trim($_POST['ssname'] ?? '');
    $sFullName = trim("$slname $sfname $smname $ssname");

    // 3. PhilHealth & NHTS
    $philStatus = trim($_POST['philStatusInput'] ?? '');
    $philNum    = trim($_POST['philNum'] ?? '');
    $prinMem    = trim($_POST['prinMem'] ?? '');
    $yakapmem   = trim($_POST['yakapmem'] ?? '');

    // Validation Checks
    if ($firstname === '' || $lastname === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please provide First and Last Name') . '&type=error');
        exit();
    } 

    if ($sFullName === ''){
      $sFullName = NULL;
    }

    if ($suffix === ''){
      $suffix = NULL;
    }

    if ($address === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please provide the address') . '&type=error');
        exit();
    }
    
    if ($religion === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please select Religion') . '&type=error');
        exit();
    }

    if ($gender === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please select the gender') . '&type=error');
        exit();
    }

    if ($contactnum === '' || strlen($contactnum) < 11){
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please provide a valid numeric contact number') . '&type=error');
        exit();
    }

    if ($employmentStatus === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please select an Employment status') . '&type=error');
        exit();
    } else if (strtolower($employmentStatus) === 'employed' && $occupation === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please provide occupation') . '&type=error');
        exit();
    } else if (strtolower($employmentStatus) === 'self-employed' && $business === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please provide business') . '&type=error');
        exit();
    } else {
      $occupation = NULL;
      $business = NULL;
    }

    if ($civilStatus === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please select civil status') . '&type=error');
        exit();
    }

    // family info validation
    if ($mfname === '' || $mlname === '' || $ffname === '' || $flname === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please provide parent names') . '&type=error');
        exit();
    }

    // philhealth validation
    if ($philStatus === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please select PhilHealth Status') . '&type=error');
        exit();
    } else if (strtolower($philStatus) === 'member' && $philNum === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please provide PhilHealth Number') . '&type=error');
        exit();
    } else if (strtolower($philStatus) === 'dependent' && ($philNum === '' || $prinMem === '')) {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please provide PhilHealth Number and Principal Member') . '&type=error');
        exit();
    } else {
      $philNum = NULL;
      $prinMem = NULL;
    }

    // yakap validation
    if ($yakapmem === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please select Yakap Member.') . '&type=error');
        exit();
    }

    // Duplicate Check
    $check = $conn->prepare('SELECT patient_id FROM patient_records WHERE firstname = ? AND lastname = ? AND middlename = ? AND suffix = ?');
    if (!$check) {
        die("Duplicate Check Error: " . $conn->error);
    }
    
    $check->bind_param('ssss', $firstname, $lastname, $middlename, $suffix);
    $check->execute();
    $res = $check->get_result();

    if ($res && $res->num_rows > 0) {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Patient Already Existing') . '&type=error');
        exit();
    }
    $check->close();

    // Insert Query
    $sql = "INSERT INTO patient_records (
        firstname, lastname, middlename, suffix, address, age, gender, 
        date_of_birth, contact_number, email, employment_status, occupation, 
        business_name, civil_status, religion, mothers_name, fathers_name, 
        spouses_name, phil_status, phil_number, principal_member, yakap_member
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $insertstmt = $conn->prepare($sql);

    if (!$insertstmt) {
        die("Database Prepare Failed: " . $conn->error);
    }
    
    $insertstmt->bind_param("sssssissssssssssssssss", 
        $firstname, $lastname, $middlename, $suffix, $address, 
        $age, 
        $gender, $birthDate, $contactnum, $emailContact, 
        $employmentStatus, $occupation, $business, $civilStatus, 
        $religion, $mFullName, $fFullName, $sFullName, 
        $philStatus, $philNum, $prinMem, $yakapmem
    );

    if ($insertstmt->execute()) {
        // Clear saved form draft on successful insertion
        unset($_SESSION['form_data']);
        header('Location: ../pages/login_page.php?msg=' . urlencode('Successfully Registered') . '&type=success');
        exit();
    } else {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Error registering patient: ' . $insertstmt->error) . '&type=error');
        exit();
    }

} else {
    header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Invalid Request Method') . '&type=error');
    exit();
}
?>