<?php
session_start();
header('Content-Type: application/json');
require_once '../php/connection.php';
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

/**
 * Generates a cryptographically secure, unique 6-digit account token
 */
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

$action = $_POST['action'] ?? '';

// -------------------------------------------------------------
// ACTION 1: Check Duplication, Generate Token & Save Draft Session
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

    // Validations (Contact is optional now)
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
    if ($contactnum !== '' && strlen(preg_replace('/\D/', '', $contactnum)) < 11) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide a valid 11-digit contact number']);
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

        

    $address_id = 0;

    if ($address === '') {
        header('Location: ../pages/patient_reg_form.php?msg=' . urlencode('Please provide the address') . '&type=error');
        exit();
    } else {
        // Ensure the table and column names match your database schema
        $barangayCheck = $conn->prepare("SELECT barangay_id FROM barangay WHERE barangay_address = ? LIMIT 1");
        $barangayCheck->bind_param("s", $address);
        $barangayCheck->execute();
        $barangayRes = $barangayCheck->get_result();
        
        if ($barangayRes->num_rows === 0) {
            $barangayCheck->close();
            header('Location: ../pages/patient_records.php?msg=' . urlencode('Please select a valid barangay from the list.') . '&type=error');
            exit();
        } else {
            // FIX: Fetch the row into an array first
            $row = $barangayRes->fetch_assoc();
            $address_id = $row['barangay_id'];
        }
        $barangayCheck->close();
    }



    // Generate token if not already in session (ensures token stays unchangeable)
    if (empty($_SESSION['patient_draft']['account_token'])) {
        $accountToken = generateUniqueAccountToken($conn);
    } else {
        $accountToken = $_SESSION['patient_draft']['account_token'];
    }

    // Save draft data including the generated token
    $_SESSION['patient_draft'] = [
        'account_token'     => $accountToken,
        'firstname'         => $firstname,
        'lastname'          => $lastname,
        'middlename'        => ($middlename === '') ? NULL : $middlename,
        'suffix'            => ($suffix === '') ? NULL : $suffix,
        'address'           => $address_id,
        'age'               => $age,
        'gender'            => $gender,
        'birthDate'         => $birthDate,
        'contactnum'        => ($contactnum === '') ? NULL : $contactnum,
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

    echo json_encode([
        'status' => 'success', 
        'message' => 'Patient details saved.',
        'account_token' => $accountToken
    ]);
    exit();
}

// -------------------------------------------------------------
// ACTION 2: Save Patient Details with Token & Register Credentials
// -------------------------------------------------------------
if ($action === 'final_register') {

    if (!isset($_SESSION['patient_draft'])) {
        echo json_encode(['status' => 'error', 'message' => 'Patient draft session expired. Please refill patient information.']);
        exit();
    }

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? ''); // Optional email
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in Username and Password.']);
        exit();
    }

    // Check unique username (and unique non-empty email)
    if ($email !== '') {
        $userCheck = $conn->prepare("SELECT patient_auth_id FROM patient_auth WHERE username = ? OR email = ? LIMIT 1");
        $userCheck->bind_param('ss', $username, $email);
    } else {
        $userCheck = $conn->prepare("SELECT patient_auth_id FROM patient_auth WHERE username = ? LIMIT 1");
        $userCheck->bind_param('s', $username);
    }
    
    $userCheck->execute();
    $userRes = $userCheck->get_result();

    if ($userRes && $userRes->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username or Email is already taken.']);
        exit();
    }
    $userCheck->close();

    // Transaction
    $conn->begin_transaction();
    $set_token = NULL;
    try {
        $draft = $_SESSION['patient_draft'];

        // 1. Insert into patient_records including account_token
        $sql = "INSERT INTO patient_records (
            account_token, firstname, lastname, middlename, suffix, address, age, gender, 
            date_of_birth, contact_number, email, employment_status, occupation, 
            business_name, civil_status, religion, mothers_name, fathers_name, 
            spouses_name, phil_status, phil_number, principal_member, yakap_member
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $insertstmt = $conn->prepare($sql);
        if (!$insertstmt) {
            throw new Exception("Database Prepare Failed: " . $conn->error);
        }

        $formattedEmail = ($email === '') ? NULL : $email;
        $set_token = $draft['account_token'];
        $insertstmt->bind_param("isssssissssssssssssssss", 
            $draft['account_token'], $draft['firstname'], $draft['lastname'], $draft['middlename'], $draft['suffix'], $draft['address'], 
            $draft['age'], $draft['gender'], $draft['birthDate'], $draft['contactnum'], $formattedEmail,
            $draft['employmentStatus'], $draft['occupation'], $draft['business'], $draft['civilStatus'], 
            $draft['religion'], $draft['mothers_name'], $draft['fathers_name'], $draft['spouses_name'], 
            $draft['philStatus'], $draft['philNum'], $draft['prinMem'], $draft['yakapmem']
        );

        if (!$insertstmt->execute()) {
            throw new Exception("Failed to insert patient details: " . $insertstmt->error);
        }
        
        $registered_patient_id = $conn->insert_id;
        $insertstmt->close();

        // 2. Hash Password & Insert into patient_auth
        $options = ["cost" => 12];
        $hashed_password = password_hash($password, PASSWORD_BCRYPT, $options);

        $mInit = !empty($draft['middlename']) ? ' ' . $draft['middlename'] . ' ' : ' ';
        $status = "Offline";
        $role = "Patient";

        $authStmt = $conn->prepare("INSERT INTO patient_auth (patient_id, username, password_hash, lastname, firstname, middlename, suffix, email, account_token, status, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $authStmt->bind_param('issiiiiiiss', $registered_patient_id, $username, $hashed_password, $registered_patient_id, $registered_patient_id, $registered_patient_id, $registered_patient_id, $registered_patient_id, $set_token, $status, $role);
        
        if (!$authStmt->execute()) {
            throw new Exception("Failed to save patient account: " . $authStmt->error);
        }
        $authStmt->close();

        $conn->commit();
        unset($_SESSION['patient_draft']);

        echo json_encode(['status' => 'success', 'message' => 'Account created successfully! Account Token: ' . $draft['account_token']]);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit();
    }
}
?>