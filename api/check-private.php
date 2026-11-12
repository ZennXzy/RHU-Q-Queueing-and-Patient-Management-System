
<?php
// --Session--
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Manila');

// --Dependencies--
require_once __DIR__ . '/../php/connection.php';
require_once __DIR__ . '/../php/session-owner.php';

// --Configuration--
const ENABLE_CLOSING_TIME_LOGOUT = true;
const CLOSING_TIME = '23:59';

$isAjax = isset($_GET['ajax_check']);

// --Response Helper--
function sessionResponse(
    string $status,
    string $message = 'Session Expired',
    int $code = 401,
    ?string $reason = null
): void {
    global $isAjax;

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        http_response_code($code);

        $response = [
            'status' => $status,
            'message' => $message
        ];

        if ($reason !== null) {
            $response['reason'] = $reason;
        }

        echo json_encode($response);
        exit();
    }

    if ($status === 'active') {
        return;
    }

    // Do not redirect on temporary server errors.
    if ($code >= 500) {
        http_response_code($code);
        exit('Session verification temporarily unavailable.');
    }

    $params = http_build_query([
        'msg' => $message,
        'type' => 'error'
    ]);

    header('Location: ../pages/login_page.php?' . $params);
    exit();
}

// --Clear Current Session--
function clearCurrentSession(): void {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]
        );
    }

    session_destroy();
}

// --Validate Session--
$userId = filter_var(
    $_SESSION['logged_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$username = $_SESSION['logged_name'] ?? null;

$userType = strtolower(
    trim((string) ($_SESSION['user_type'] ?? ''))
);

$ownershipToken = $_SESSION['ownership_token'] ?? null;

if (
    !$userId ||
    !is_string($username) ||
    trim($username) === '' ||
    !in_array($userType, ['admin', 'patient'], true) ||
    !is_string($ownershipToken) ||
    !preg_match('/^[a-f0-9]{64}$/D', $ownershipToken)
) {
    clearCurrentSession();

    sessionResponse(
        'unauthenticated',
        'Session Expired'
    );
}

// --Database Timezone--
if (!$conn->query("SET time_zone = '+08:00'")) {
    error_log('Session timezone error: ' . $conn->error);

    sessionResponse(
        'error',
        'Session verification temporarily unavailable.',
        503
    );
}

// --Account Table--
if ($userType === 'patient') {
    $table = 'patient_auth';
    $idColumn = 'patient_auth_id';
} else {
    $table = 'admin_auth';
    $idColumn = 'id';
}

// --Verification State--
$verification = 'error';
$verificationMessage = 'Session verification unavailable.';
$verificationCode = 503;
$verificationReason = null;

// --Verify Ownership--
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
            &$verification,
            &$verificationMessage,
            &$verificationCode,
            &$verificationReason
        ) {
            // --Ownership Record--
            if ($record === null) {
                $verification = 'unauthenticated';
                $verificationMessage = 'Session ownership not found.';
                $verificationCode = 401;
                return;
            }

            // --Current Session Owner--
            if (!rhuOwnerMatches($record, $ownershipToken)) {
                $verification = 'logged_out';
                $verificationMessage =
                    'This session has been replaced.';
                $verificationCode = 401;
                return;
            }

            // --Database Account--
            $sql = "
                SELECT status
                FROM {$table}
                WHERE {$idColumn} = ?
                  AND username = ?
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new RuntimeException(
                    'Unable to prepare account verification.'
                );
            }

            $stmt->bind_param('is', $userId, $username);

            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();
                throw new RuntimeException($error);
            }

            $account = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // --Missing Account--
            if (!$account) {
                $verification = 'unauthenticated';
                $verificationMessage = 'Account no longer exists.';
                $verificationCode = 401;
                return;
            }

            // --Offline Account--
            if (
                strcasecmp(
                    trim((string) ($account['status'] ?? '')),
                    'Online'
                ) !== 0
            ) {
                $verification = 'logged_out';
                $verificationMessage = 'Account is no longer online.';
                $verificationCode = 401;
                return;
            }

            // --Closing Time--
            if (
                ENABLE_CLOSING_TIME_LOGOUT &&
                date('H:i') >= CLOSING_TIME
            ) {
                // This update and ownership removal are
                // serialized against other operations
                // using the same account lock.

                $updateSql = "
                    UPDATE {$table}
                    SET status = 'Offline'
                    WHERE {$idColumn} = ?
                      AND username = ?
                      AND status = 'Online'
                ";

                $updateStmt = $conn->prepare($updateSql);

                if (!$updateStmt) {
                    throw new RuntimeException(
                        'Unable to prepare closing-time logout.'
                    );
                }

                $updateStmt->bind_param(
                    'is',
                    $userId,
                    $username
                );

                if (!$updateStmt->execute()) {
                    $error = $updateStmt->error;
                    $updateStmt->close();
                    throw new RuntimeException($error);
                }

                $updateStmt->close();

                // --Remove Current Ownership--
                rhuOwnerRemoveLocked(
                    $path,
                    $record,
                    $ownershipToken
                );

                $verification = 'logged_out';
                $verificationMessage =
                    'System closed for the day. Logged out.';
                $verificationCode = 401;
                $verificationReason = 'Clinic closed';
                return;
            }

            // --Authorized Session--
            $verification = 'active';
            $verificationMessage = 'Session active.';
            $verificationCode = 200;
        }
    );

} catch (Throwable $error) {
    error_log(
        '[RHU-Q Session Check] ' . $error->getMessage()
    );

    sessionResponse(
        'error',
        'Session verification temporarily unavailable.',
        503
    );
}

// --Handle Invalid Session--
if ($verification !== 'active') {
    if ($verificationCode === 401) {
        clearCurrentSession();
    }

    sessionResponse(
        $verification,
        $verificationMessage,
        $verificationCode,
        $verificationReason
    );
}

// --AJAX Session Check--
if ($isAjax) {
    sessionResponse(
        'active',
        'Session active.',
        200
    );
}

// --Role Authorization--
function authorizeRoles(array $allowedRoles): void {
    $userRole = $_SESSION['logged_role'] ?? '';

    // --Admin Access--
    if ($userRole === 'Admin') {
        return;
    }

    // --Allowed Role--
    if (in_array($userRole, $allowedRoles, true)) {
        return;
    }

    // --Unauthorized Redirect--
    $redirects = [
        'Display' => '../pages/queue_display_page.php',
        'Doctor/Staff' => '../pages/queue_manager_page.php',
        'Patient' => '../pages/queue_display_page.php'
    ];

    $destination = $redirects[$userRole]
        ?? '../pages/login_page.php';

    $params = http_build_query([
        'msg' => 'Unauthorized access.',
        'type' => 'error'
    ]);

    header('Location: ' . $destination . '?' . $params);
    exit();
}

// --Record Management Permission--
function canManageRecords(): bool {
    return ($_SESSION['logged_role'] ?? '') === 'Admin';
}
?>
