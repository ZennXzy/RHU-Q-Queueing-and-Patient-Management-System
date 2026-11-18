
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Manila');

require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/session-owner.php';

const ENABLE_STALE_SESSION_RECOVERY = true;
const LOGIN_RECOVERY_SECONDS = 900;

// --Login Redirect--
function loginRedirect($message, $type = 'error') {
    $params = http_build_query([
        'msg' => $message,
        'type' => $type
    ]);

    header('Location: ../pages/login_page.php?' . $params);
    exit();
}

// --Validate Request--
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['login'])) {
    loginRedirect('Invalid login request.');
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';

if ($username === '' || !is_string($password) || $password === '') {
    loginRedirect('Username and password are required.');
}

// --Database Timezone--
try {
    if (!$conn->query("SET time_zone = '+08:00'")) {
        throw new RuntimeException($conn->error);
    }
} catch (Throwable $error) {
    error_log('[RHU-Q Login Timezone] ' . $error->getMessage());
    loginRedirect('Unable to process login.');
}

// --Find Admin Account--
try {
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

    $stmt->bind_param('s', $username);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

} catch (Throwable $error) {
    error_log('[RHU-Q Admin Lookup] ' . $error->getMessage());
    loginRedirect('Unable to process login.');
}

$userType = $user ? 'admin' : null;

// --Find Patient Account--
if (!$user) {
    try {
        $stmt = $conn->prepare("
            SELECT
                patient_auth_id AS id,
                patient_id,
                username,
                password_hash,
                status,
                role,
                account_token,
                last_active
            FROM patient_auth
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->bind_param('s', $username);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            $userType = 'patient';
        }

    } catch (Throwable $error) {
        error_log('[RHU-Q Patient Lookup] ' . $error->getMessage());
        loginRedirect('Unable to process login.');
    }
}

// --Verify Credentials--
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

// --Regenerate Session--
if (!session_regenerate_id(true)) {
    loginRedirect('Unable to create a secure session.');
}

// --Session Ownership--
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
                // --Verify Account Status--
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

                $isOnline = strcasecmp(
                    trim((string) $account['status']),
                    'Online'
                ) === 0;

                // --Stale Session Recovery--
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

                    $databaseIsStale = (
                        $databaseInactiveSeconds >= LOGIN_RECOVERY_SECONDS
                    );

                    if ($record !== null) {
                        $lastSeen = $record['last_seen'];
                        $ownerInactiveSeconds = time() - (int) $lastSeen;

                        $ownerIsStale = (
                            $ownerInactiveSeconds >= LOGIN_RECOVERY_SECONDS
                        );
                    } else {
                        $ownerIsStale = true;
                    }

                    if (!$databaseIsStale || !$ownerIsStale) {
                        $conn->rollback();
                        return;
                    }

                    $loginRecovered = true;
                }

                // --Update Account Status--
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

                    throw new RuntimeException(
                        'Unable to update login: ' . $error
                    );
                }

                $affectedRows = $updateStmt->affected_rows;
                $updateStmt->close();

                if ($affectedRows !== 1) {
                    throw new RuntimeException(
                        'Login update affected no account.'
                    );
                }

                // --Create Session Ownership--
                $previousRecord = $record;

                rhuOwnerCreateLocked($path, $ownershipToken);

                try {
                    if (!$conn->commit()) {
                        throw new RuntimeException(
                            'Login transaction commit failed.'
                        );
                    }
                } catch (Throwable $commitError) {
                    try {
                        if ($previousRecord !== null) {
                            rhuOwnerWriteLocked($path, $previousRecord);
                        } elseif (is_file($path)) {
                            if (!unlink($path)) {
                                throw new RuntimeException(
                                    'Unable to remove new owner.'
                                );
                            }
                        }
                    } catch (Throwable $restoreError) {
                        error_log(
                            '[RHU-Q Login Ownership Restore] ' .
                            $restoreError->getMessage()
                        );
                    }

                    throw $commitError;
                }

                $loginAccepted = true;

            } catch (Throwable $error) {
                try {
                    $conn->rollback();
                } catch (Throwable $rollbackError) {
                    error_log(
                        '[RHU-Q Login Rollback] ' .
                        $rollbackError->getMessage()
                    );
                }

                throw $error;
            }
        }
    );

} catch (Throwable $error) {
    error_log(
        '[RHU-Q Login Ownership] ' .
        $error->getMessage()
    );

    $loginError = 'Unable to start your session.';
}

// --Check Login Result--
if (!$loginAccepted) {
    loginRedirect(
        $loginError ?? 'Account is already active on another device.'
    );
}

// --Clear Previous Session Data--
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

// --Set Session Data--
$_SESSION['logged_id'] = $userId;
$_SESSION['logged_name'] = $user['username'];
$_SESSION['logged_role'] = $user['role'];
$_SESSION['user_type'] = $userType;
$_SESSION['ownership_token'] = $ownershipToken;
$_SESSION['login_time'] = time();
$_SESSION['last_activity'] = time();

// --Patient Session--
if ($userType === 'patient') {
    $_SESSION['patient_id'] = $user['patient_id'];

    $_SESSION['logged_account'] = $user['username'];

} else {
    // --Admin Session--
    $_SESSION['logged_account'] = $user['fullname'];
}

// --Login Message--
$message = $loginRecovered
    ? 'Recovered an inactive session.'
    : 'Logged in successfully.';

// --Login Audit Logging--
try {
    $roleType = $_SESSION['logged_role'];

    $insertLog = $conn->prepare("
        INSERT INTO logs (
            user_id,
            user_type,
            log_message
        )
        VALUES (?, ?, ?)
    ");

    if (!$insertLog) {
        throw new RuntimeException(
            'Unable to prepare login audit log.'
        );
    }

    try {
        $insertLog->bind_param(
            'iss',
            $userId,
            $roleType,
            $message
        );

        if (!$insertLog->execute()) {
            throw new RuntimeException(
                'Unable to insert login audit log: ' .
                $insertLog->error
            );
        }
    } finally {
        $insertLog->close();
    }

} catch (Throwable $error) {
    error_log(
        '[RHU-Q Login Audit] ' .
        $error->getMessage()
    );
}

// --Save Session--
session_write_close();

// --Redirect to Launch Page--
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
