<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';

if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$action = $_POST['action'] ?? '';

// -------------------------------------------------------------
// ACTION 1: Check Duplication & Save Patient Data to Draft Session
// -------------------------------------------------------------
if ($action === 'check_and_draft') {

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
    
    // Family Info
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

    // PhilHealth & Yakap
    $philStatus = trim($_POST['philStatusInput'] ?? '');
    $philNum    = trim($_POST['philNumber'] ?? '');
    $prinMem    = trim($_POST['prinMember'] ?? '');
    $yakapmem   = trim($_POST['yakapmem'] ?? '');

    // Form Validations
    if ($firstname === '' || $lastname === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please provide First and Last Name']);
        exit();
    }
    if ($address === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please provide the address']);
        exit();
    }
    if ($religion === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please select Religion']);
        exit();
    }
    if ($gender === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please select gender']);
        exit();
    }
    if ($birthDate === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please select Date of Birth']);
        exit();
    }
    if ($contactnum === '' || strlen($contactnum) < 11) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide a valid numeric contact number']);
        exit();
    }
    if ($employmentStatus === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please select Employment status']);
        exit();
    } else if (strtolower($employmentStatus) === 'employed' && $occupation === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please provide occupation']);
        exit();
    } else if (strtolower($employmentStatus) === 'self-employed' && $business === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please provide business']);
        exit();
    }
    if ($civilStatus === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please select civil status']);
        exit();
    }
    if ($philStatus === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please select PhilHealth Status']);
        exit();
    } else if (strtolower($philStatus) === 'member' && $philNum === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please provide PhilHealth Number']);
        exit();
    } else if (strtolower($philStatus) === 'dependent' && ($philNum === '' || $prinMem === '')) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide PhilHealth Number and Principal Member']);
        exit();
    }
    if ($yakapmem === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please select Yakap Member status']);
        exit();
    }

    // Check Duplicate Patient Record
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

    $check->bind_param('sssss', $firstname, $lastname, $birthDate, $middlename, $mFullName);
    $check->execute();
    $res = $check->get_result();

    if ($res && $res->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Patient Record Already Exists!']);
        exit();
    }
    $check->close();

    // Store in Session Draft
    $_SESSION['patient_draft'] = [
        'firstname'         => $firstname,
        'lastname'          => $lastname,
        'middlename'        => ($middlename === '') ? NULL : $middlename,
        'suffix'            => ($suffix === '') ? NULL : $suffix,
        'address'           => $address,
        'age'               => $age,
        'gender'            => $gender,
        'birthDate'        => $birthDate,
        'contactnum'        => $contactnum,
        'employmentStatus'  => $employmentStatus,
        'occupation'        => (strtolower($employmentStatus) === 'employed') ? $occupation : NULL,
        'business'          => (strtolower($employmentStatus) === 'self-employed') ? $business : NULL,
        'civilStatus'       => $civilStatus,
        'religion'          => $religion,
        'mothers_name'      => $mFullName,
        'fathers_name'      => $fFullName,
        'spouses_name'      => (in_array(strtolower($civilStatus), ['married', 'widow']) && $sFullName !== '') ? $sFullName : NULL,
        'philStatus'        => $philStatus,
        'philNum'           => (strtolower($philStatus) !== 'not applicable') ? $philNum : NULL,
        'prinMem'           => (strtolower($philStatus) === 'dependent') ? $prinMem : NULL,
        'yakapmem'          => $yakapmem
    ];

    echo json_encode(['status' => 'success', 'message' => 'Patient details saved. Proceeding to credentials step.']);
    exit();
}
// -------------------------------------------------------------
// ACTION 2: Save Patient Details First, Get ID, Then Save Credentials
// -------------------------------------------------------------
if ($action === 'final_register') {

    if (!isset($_SESSION['patient_draft'])) {
        echo json_encode(['status' => 'error', 'message' => 'Patient draft session expired. Please refill patient information.']);
        exit();
    }

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in Username, Email, and Password.']);
        exit();
    }

    // Check unique username or email in patient_auth
    $userCheck = $conn->prepare("SELECT patient_auth_id FROM patient_auth WHERE username = ? OR email = ? LIMIT 1");
    $userCheck->bind_param('ss', $username, $email);
    $userCheck->execute();
    $userRes = $userCheck->get_result();

    if ($userRes && $userRes->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username or Email is already taken.']);
        exit();
    }
    $userCheck->close();

    // Begin DB Transaction
    $conn->begin_transaction();

    try {
        // 1. Insert into patient_records first using draft data
        $draft = $_SESSION['patient_draft'];

        $sql = "INSERT INTO patient_records (
            firstname, lastname, middlename, suffix, address, age, gender, 
            date_of_birth, contact_number, email, employment_status, occupation, 
            business_name, civil_status, religion, mothers_name, fathers_name, 
            spouses_name, phil_status, phil_number, principal_member, yakap_member
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $insertstmt = $conn->prepare($sql);
        if (!$insertstmt) {
            throw new Exception("Database Prepare Failed: " . $conn->error);
        }

        $insertstmt->bind_param("sssssissssssssssssssss", 
            $draft['firstname'], $draft['lastname'], $draft['middlename'], $draft['suffix'], $draft['address'], 
            $draft['age'], $draft['gender'], $draft['birthDate'], $draft['contactnum'], $email,
            $draft['employmentStatus'], $draft['occupation'], $draft['business'], $draft['civilStatus'], 
            $draft['religion'], $draft['mothers_name'], $draft['fathers_name'], $draft['spouses_name'], 
            $draft['philStatus'], $draft['philNum'], $draft['prinMem'], $draft['yakapmem']
        );

        if (!$insertstmt->execute()) {
            throw new Exception("Failed to insert patient details: " . $insertstmt->error);
        }
        
        // 2. Retrieve newly generated patient_id
        $registered_patient_id = $conn->insert_id;
        $insertstmt->close();

        // 3. Hash Password & Insert credentials into patient_auth with patient_id
        $options = ["cost" => 12];
        $hashed_password = password_hash($password, PASSWORD_BCRYPT, $options);

        // Concatenate full name cleanly with single dots and spaces
        $mInit = !empty($draft['middlename']) ? ' ' . $draft['middlename'] . ' ' : ' ';
        $fullname = trim($draft['firstname'] . $mInit . $draft['lastname']);
        $status = "Offline";
        $role = "Patient";

        $authStmt = $conn->prepare("INSERT INTO patient_auth (patient_id, username, password_hash, fullname, email, status, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $authStmt->bind_param('issssss', $registered_patient_id, $username, $hashed_password, $fullname, $registered_patient_id, $status, $role);
        
        if (!$authStmt->execute()) {
            throw new Exception("Failed to save patient account: " . $authStmt->error);
        }
        $authStmt->close();

        // Commit Transaction
        $conn->commit();
        unset($_SESSION['patient_draft']);

        echo json_encode(['status' => 'success', 'message' => 'Account and Patient record successfully created!']);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit();
    }
}
?>