<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Manila');

require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/session-owner.php';

const ENABLE_STALE_SESSION_RECOVERY = true;
const LOGIN_RECOVERY_SECONDS = 900;

function loginRedirect($message, $type = 'error') {
    $params = http_build_query([
        'msg' => $message,
        'type' => $type
    ]);

    header('Location: ../pages/login_page.php?' . $params);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['login'])) {
    loginRedirect('Invalid login request.');
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';

if ($username === '' || !is_string($password) || $password === '') {
    loginRedirect('Username and password are required.');
}

if (!$conn->query("SET time_zone = '+08:00'")) {
    error_log('Login timezone error: ' . $conn->error);
    loginRedirect('Unable to process login.');
}

$stmt = $conn->prepare("
    SELECT
        id,
        username,
        fullname,
        password_hash,
        status,
        role,
        last_active
    FROM admin_auth
    WHERE username = ?
    LIMIT 1
");

if (!$stmt) {
    error_log('Admin lookup failed: ' . $conn->error);
    loginRedirect('Unable to process login.');
}

$stmt->bind_param('s', $username);

if (!$stmt->execute()) {
    $stmt->close();
    loginRedirect('Unable to process login.');
}

$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$userType = $user ? 'admin' : null;

if (!$user) {
    $stmt = $conn->prepare("
        SELECT
            patient_auth_id AS id,
            username,
            password_hash,
            status,
            role,
            account_token,
            last_active,
            patient_id,
        FROM patient_auth
        WHERE username = ?
        LIMIT 1
    ");

    if (!$stmt) {
        error_log('Patient lookup failed: ' . $conn->error);
        loginRedirect('Unable to process login.');
    }

    $stmt->bind_param('s', $username);

    if (!$stmt->execute()) {
        $stmt->close();
        loginRedirect('Unable to process login.');
    }

    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user) {
        $userType = 'patient';
    }
}

if (!$user || !password_verify($password, $user['password_hash'])) {
    loginRedirect('Invalid username or password.');
}

$userId = (int) $user['id'];

if ($userType === 'admin') {
    $table = 'admin_auth';
    $idColumn = 'id';
} else {
    $table = 'patient_auth';
    $idColumn = 'patient_auth_id';
}

if (!session_regenerate_id(true)) {
    loginRedirect('Unable to create a secure session.');
}

$ownershipToken = rhuOwnerNewToken();

$loginAccepted = false;
$loginRecovered = false;
$loginError = null;

try {
    rhuOwnerWithLock(
        $userType,
        $userId,
        function ($record, $path) use (
            $conn,
            $table,
            $idColumn,
            $userId,
            $username,
            $ownershipToken,
            &$loginAccepted,
            &$loginRecovered
        ) {
            $conn->begin_transaction();

            try {
                $sql = "
                    SELECT status, last_active
                    FROM {$table}
                    WHERE {$idColumn} = ?
                      AND username = ?
                    LIMIT 1
                    FOR UPDATE
                ";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new RuntimeException('Unable to prepare account lookup.');
                }

                $stmt->bind_param('is', $userId, $username);
                if (!$stmt->execute()) {
                    $stmt->close();
                    throw new RuntimeException('Unable to verify account.');
                }

                $account = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$account) {
                    throw new RuntimeException('Account no longer exists.');
                }

                $isOnline = strcasecmp(trim((string) $account['status']), 'Online') === 0;

                if ($isOnline) {
                    if (!ENABLE_STALE_SESSION_RECOVERY) {
                        $conn->rollback();
                        return;
                    }

                    $lastActive = $account['last_active'];
                    if (!is_string($lastActive) || trim($lastActive) === '') {
                        $conn->rollback();
                        return;
                    }

                    $lastActiveTime = strtotime($lastActive);
                    if ($lastActiveTime === false) {
                        $conn->rollback();
                        return;
                    }

                    $databaseInactiveSeconds = time() - $lastActiveTime;
                    $databaseIsStale = ($databaseInactiveSeconds >= LOGIN_RECOVERY_SECONDS);

                    if ($record !== null) {
                        $lastSeen = $record['last_seen'];
                        $ownerInactiveSeconds = time() - (int) $lastSeen;
                        $ownerIsStale = ($ownerInactiveSeconds >= LOGIN_RECOVERY_SECONDS);
                    } else {
                        $ownerIsStale = true;
                    }

                    if (!$databaseIsStale || !$ownerIsStale) {
                        $conn->rollback();
                        return;
                    }

                    $loginRecovered = true;
                }

                $updateSql = "
                    UPDATE {$table}
                    SET status = 'Online',
                        last_active = NOW()
                    WHERE {$idColumn} = ?
                      AND username = ?
                ";

                $updateStmt = $conn->prepare($updateSql);
                if (!$updateStmt) {
                    throw new RuntimeException('Unable to prepare login update.');
                }

                $updateStmt->bind_param('is', $userId, $username);
                if (!$updateStmt->execute()) {
                    $error = $updateStmt->error;
                    $updateStmt->close();
                    throw new RuntimeException('Unable to update login: ' . $error);
                }

                $affectedRows = $updateStmt->affected_rows;
                $updateStmt->close();

                if ($affectedRows !== 1) {
                    throw new RuntimeException('Login update affected no account.');
                }

                $previousRecord = $record;
                rhuOwnerCreateLocked($path, $ownershipToken);

                try {
                    if (!$conn->commit()) {
                        throw new RuntimeException('Login transaction commit failed.');
                    }
                } catch (Throwable $commitError) {
                    try {
                        if ($previousRecord !== null) {
                            rhuOwnerWriteLocked($path, $previousRecord);
                        } elseif (is_file($path)) {
                            if (!unlink($path)) {
                                throw new RuntimeException('Unable to remove new owner.');
                            }
                        }
                    } catch (Throwable $restoreError) {
                        error_log('Login ownership restore error: ' . $restoreError->getMessage());
                    }

                    throw $commitError;
                }

                $loginAccepted = true;

            } catch (Throwable $error) {
                try {
                    $conn->rollback();
                } catch (Throwable $rollbackError) {
                    error_log('Login rollback error: ' . $rollbackError->getMessage());
                }

                throw $error;
            }
        }
    );

} catch (Throwable $error) {
    error_log('RHU-Q login ownership error: ' . $error->getMessage());
    $loginError = 'Unable to start your session.';
}

if (!$loginAccepted) {
    loginRedirect($loginError ?? 'Account is already active on another device.');
}

unset(
    $_SESSION['logged_id'],
    $_SESSION['logged_name'],
    $_SESSION['logged_role'],
    $_SESSION['logged_account'],
    $_SESSION['user_type'],
    $_SESSION['patient_id'],
    $_SESSION['login_time'],
    $_SESSION['last_activity'],
    $_SESSION['ownership_token']
);

$_SESSION['logged_id'] = $userId;
$_SESSION['logged_name'] = $user['username'];
$_SESSION['logged_role'] = $user['role'];
$_SESSION['user_type'] = $userType;

$_SESSION['ownership_token'] = $ownershipToken;
$_SESSION['login_time'] = time();
$_SESSION['last_activity'] = time();

if ($userType === 'patient') {
    $nameParts = [
        $user['firstname'] ?? '',
        $user['middlename'] ?? '',
        $user['lastname'] ?? '',
        $user['suffix'] ?? ''
    ];

    $_SESSION['logged_account'] = implode(
        ' ',
        array_filter(
            $nameParts,
            static function ($value) {
                return trim((string) $value) !== '';
            }
        )
    );

    $_SESSION['patient_id'] = $user['patient_id'];
} else {
    $_SESSION['logged_account'] = $user['fullname'];
}

if ($loginRecovered) {
    $message = $user['username'] . ' (' . ucfirst($userType) . ') recovered an inactive session.';
} else {
    $message = $user['username'] . ' (' . ucfirst($userType) . ') logged in successfully.';
}

$insertLog = $conn->prepare("
    INSERT INTO logs (user_id, user_type, log_message)
    VALUES (?, ?, ?)
");

if ($insertLog) {
    $roleType = ucfirst($userType);
    $insertLog->bind_param('iss', $userId, $roleType, $message);
    $insertLog->execute();
    $insertLog->close();
}

session_write_close();

$redirect = '../pages/launch_page.php';

$params = http_build_query([
    'msg' => $loginRecovered
        ? 'Previous inactive session recovered successfully'
        : 'Logged in successfully',
    'type' => 'success'
]);

header('Location: ' . $redirect . '?' . $params);
exit();
?>