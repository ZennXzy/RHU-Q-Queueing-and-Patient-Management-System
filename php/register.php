<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';

if ($_SERVER["REQUEST_METHOD"] === 'POST' && isset($_POST['register'])){

    // Save submitted data to session so fields persist if an error occurs
    $_SESSION['form_data'] = $_POST;

    // 1. Personal Info Sanitization
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

    // 3. PhilHealth & Yakap (Corrected POST Keys)
    $philStatus = trim($_POST['philStatusInput'] ?? '');
    $philNum    = trim($_POST['philNumber'] ?? '');
    $prinMem    = trim($_POST['prinMember'] ?? '');
    $yakapmem   = trim($_POST['yakapmem'] ?? '');

    // Validation Checks
    if ($firstname === '' || $lastname === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide First and Last Name') . '&type=error');
        exit();
    } 

    if ($address === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide the address') . '&type=error');
        exit();
    }
    
    if ($religion === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select Religion') . '&type=error');
        exit();
    }

    if ($gender === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select gender') . '&type=error');
        exit();
    }

    if ($birthDate === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select Date of Birth') . '&type=error');
        exit();
    }

    if ($contactnum === '' || strlen($contactnum) < 11){
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide a valid numeric contact number') . '&type=error');
        exit();
    }


    if ($emailContact !== ''){
        list($user, $domain) = explode("@", $emailContact);
        if (!filter_var($emailContact, FILTER_VALIDATE_EMAIL) && !getmxrr($domain, $mxhosts)){
            header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide a valid email address.') . '&type=error');
            exit();
        }
    }

    if ($employmentStatus === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select Employment status') . '&type=error');
        exit();
    } else if (strtolower($employmentStatus) === 'employed' && $occupation === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide occupation') . '&type=error');
        exit();
    } else if (strtolower($employmentStatus) === 'self-employed' && $business === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide business') . '&type=error');
        exit();
    }

    if ($civilStatus === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select civil status') . '&type=error');
        exit();
    }

    if ($philStatus === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select PhilHealth Status') . '&type=error');
        exit();
    } else if (strtolower($philStatus) === 'member' && $philNum === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide PhilHealth Number') . '&type=error');
        exit();
    } else if (strtolower($philStatus) === 'dependent' && ($philNum === '' || $prinMem === '')) {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please provide PhilHealth Number and Principal Member') . '&type=error');
        exit();
    }

    if ($yakapmem === '') {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Please select Yakap Member.') . '&type=error');
        exit();
    }

    // Convert Empty Strings to NULL for DB Insertion
    $suffix       = ($suffix === '') ? NULL : $suffix;
    $middlename   = ($middlename === '') ? NULL : $middlename;
    $emailContact = ($emailContact === '') ? NULL : $emailContact;
    $occupation   = (strtolower($employmentStatus) === 'employed') ? $occupation : NULL;
    $business     = (strtolower($employmentStatus) === 'self-employed') ? $business : NULL;
    $sFullName    = (in_array(strtolower($civilStatus), ['married', 'widow']) && $sFullName !== '') ? $sFullName : NULL;
    $philNum      = (strtolower($philStatus) !== 'not applicable') ? $philNum : NULL;
    $prinMem      = (strtolower($philStatus) === 'dependent') ? $prinMem : NULL;

    function generateUniqueAccountToken($conn) {
        do {
            // Securely generate a number between 000000 and 999999
            $token = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Check uniqueness against database
            $stmt = $conn->prepare("SELECT patient_id FROM patient_records WHERE account_token = ? LIMIT 1");
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $res = $stmt->get_result();
            $exists = ($res && $res->num_rows > 0);
            $stmt->close();
        } while ($exists);

        return $token;
    }

    $accountToken = generateUniqueAccountToken($conn);

    // Robust Duplicate Record Verifier
    // Checks standard unique patient identifyers: First Name, Last Name, Birth Date, and Mother's Name
    $check = $conn->prepare("
        SELECT patient_id 
        FROM patient_records 
        WHERE LOWER(firstname) = LOWER(?) 
          AND LOWER(lastname) = LOWER(?) 
          AND date_of_birth = ?
          AND (
              LOWER(COALESCE(middlename, '')) = LOWER(?) 
              OR LOWER(COALESCE(mothers_name, '')) = LOWER(?)
          )
        LIMIT 1
    ");

    if (!$check) {
        die("Duplicate Check SQL Error: " . $conn->error);
    }
    
    $check->bind_param('sssss', $firstname, $lastname, $birthDate, $middlename, $mFullName);
    $check->execute();
    $res = $check->get_result();

    if ($res && $res->num_rows > 0) {
        header('Location: ../pages/register_page.php?msg=' . urlencode('Patient Record Already Exists!') . '&type=error');
        exit();
    }
    $check->close();

    // Insert Query
    $sql = "INSERT INTO patient_records (
        firstname, lastname, middlename, suffix, address, age, gender, 
        date_of_birth, contact_number, email, account_token, employment_status, occupation, 
        business_name, civil_status, religion, mothers_name, fathers_name, 
        spouses_name, phil_status, phil_number, principal_member, yakap_member
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $insertstmt = $conn->prepare($sql);

    if (!$insertstmt) {
        die("Database Prepare Failed: " . $conn->error);
    }
    
    $insertstmt->bind_param("sssssisssssssssssssssss", 
        $firstname, $lastname, $middlename, $suffix, $address, 
        $age, 
        $gender, $birthDate, $contactnum, $emailContact, $accountToken, 
        $employmentStatus, $occupation, $business, $civilStatus, 
        $religion, $mFullName, $fFullName, $sFullName, 
        $philStatus, $philNum, $prinMem, $yakapmem
    );

    if ($insertstmt->execute()) {
        unset($_SESSION['form_data']);
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