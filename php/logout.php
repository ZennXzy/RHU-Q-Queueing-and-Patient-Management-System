
<?php
// --Session--
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Manila');

// --Dependencies--
require_once __DIR__ . '/session-owner.php';
// --Database Connection--
require_once __DIR__ . '/connection.php';

// --Configuration--
const LOGOUT_REDIRECT = '../pages/login_page.php';

// --Clear PHP Session--
function clearLogoutSession(): void {
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

// --Redirect Helper--
function logoutRedirect(
    string $message,
    string $type = 'info'
): void {
    $params = http_build_query([
        'msg' => $message,
        'type' => $type
    ]);

    header(
        'Location: ' .
        LOGOUT_REDIRECT .
        '?' . $params
    );

    exit();
}

// --Validate Request--
// Logout changes authentication state.
// Require POST rather than a GET navigation.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

// --Validate Same-Origin Request--
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin !== '') {
    $parts = parse_url($origin);
    $originHost = $parts['host'] ?? '';
    $requestHost = $_SERVER['HTTP_HOST'] ?? '';

    $expectedScheme = (
        !empty($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] !== 'off'
    ) ? 'https' : 'http';

    $originScheme = $parts['scheme'] ?? '';

    if (
        !is_string($originHost) ||
        !is_string($originScheme) ||
        strcasecmp(
            $originHost,
            parse_url(
                'http://' . $requestHost,
                PHP_URL_HOST
            ) ?: ''
        ) !== 0 ||
        $originScheme !== $expectedScheme
    ) {
        http_response_code(403);
        exit('Invalid request origin.');
    }
} else {
    // --Fetch Metadata Fallback--
    $fetchSite = $_SERVER[
        'HTTP_SEC_FETCH_SITE'
    ] ?? '';

    if (
        $fetchSite !== '' &&
        !in_array(
            $fetchSite,
            ['same-origin', 'none'],
            true
        )
    ) {
        http_response_code(403);
        exit('Cross-origin logout rejected.');
    }

    // Requests without Origin or Fetch Metadata
    // should use a server-validated CSRF token
    // before production deployment.
}

// --Read Session--
$userId = filter_var(
    $_SESSION['logged_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$username = $_SESSION['logged_name'] ?? null;

$userType = strtolower(
    trim((string) (
        $_SESSION['user_type'] ?? ''
    ))
);

$ownershipToken =
    $_SESSION['ownership_token'] ?? null;

// --Validate Authentication--
$validSession = (
    $userId !== false &&
    $userId !== null &&
    is_string($username) &&
    trim($username) !== '' &&
    in_array(
        $userType,
        ['admin', 'patient'],
        true
    ) &&
    is_string($ownershipToken) &&
    preg_match(
        '/^[a-f0-9]{64}$/D',
        $ownershipToken
    ) === 1
);

// --Invalid Local Session--
if (!$validSession) {
    clearLogoutSession();

    logoutRedirect(
        'Your session has ended.'
    );
}

// --Account Table--
if ($userType === 'admin') {
    $table = 'admin_auth';
    $idColumn = 'id';
} else {
    $table = 'patient_auth';
    $idColumn = 'patient_auth_id';
}

// --Logout State--
$logoutResult = 'error';

try {
    

    if (
        !isset($conn) ||
        $conn->connect_errno
    ) {
        throw new RuntimeException(
            'Database connection unavailable.'
        );
    }

    // --Database Timezone--
    if (!$conn->query(
        "SET time_zone = '+08:00'"
    )) {
        throw new RuntimeException(
            'Database timezone unavailable.'
        );
    }

    // --Account Ownership Lock--
    rhuOwnerWithLock(
        $userType,
        $userId,
        function ($record, $path) use (
            $conn,
            $table,
            $idColumn,
            $userId,
            $userType,
            $username,
            $ownershipToken,
            &$logoutResult
        ) {
            // --Missing Ownership--
            if ($record === null) {
                $logoutResult = 'not_owner';
                return;
            }

            // --Verify Current Owner--
            if (!rhuOwnerMatches(
                $record,
                $ownershipToken
            )) {
                // Never modify the account or
                // remove another session's record.
                $logoutResult = 'not_owner';
                return;
            }

            // --Database Transaction--
            $conn->begin_transaction();

            try {
                // --Lock Account Row--
                $selectSql = "
                    SELECT status
                    FROM {$table}
                    WHERE {$idColumn} = ?
                      AND username = ?
                    LIMIT 1
                    FOR UPDATE
                ";

                $stmt = $conn->prepare(
                    $selectSql
                );

                if (!$stmt) {
                    throw new RuntimeException(
                        'Unable to prepare account lookup.'
                    );
                }

                $stmt->bind_param(
                    'is',
                    $userId,
                    $username
                );

                if (!$stmt->execute()) {
                    $error = $stmt->error;
                    $stmt->close();

                    throw new RuntimeException(
                        $error
                    );
                }

                $account = $stmt
                    ->get_result()
                    ->fetch_assoc();

                $stmt->close();

                // --Account Removed--
                if (!$account) {
                    $conn->rollback();
                    $logoutResult = 'not_owner';
                    return;
                }

                
                // --Mark Account Offline--
                if($userType === 'admin'){
                    $updateSql = "
                        UPDATE admin_auth
                        SET status = 'Offline'
                        WHERE id = ?
                        AND username = ?
                        AND status = 'Online'
                    ";
                } else {
                    $updateSql = "
                        UPDATE patient_auth
                        SET status = 'Offline'
                        WHERE patient_auth_id = ?
                        AND username = ?
                        AND status = 'Online'
                    ";
                }
                

                $updateStmt = $conn->prepare($updateSql);

                if (!$updateStmt) {
                    throw new RuntimeException(
                        'Unable to prepare logout update.'
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

                // --Verify Database Status--
                if($userType === 'admin'){
                    $verifyStmt = $conn->prepare("
                        SELECT status
                        FROM admin_auth
                        WHERE id = ?
                        AND username = ?
                        LIMIT 1
                    ");
                } else {
                    $verifyStmt = $conn->prepare("
                        SELECT status
                        FROM patient_auth
                        WHERE patient_auth_id = ?
                        AND username = ?
                        LIMIT 1
                    ");
                }
                

                if (!$verifyStmt) {
                    throw new RuntimeException(
                        'Unable to prepare logout verification.'
                    );
                }

                $verifyStmt->bind_param(
                    'is',
                    $userId,
                    $username
                );

                if (!$verifyStmt->execute()) {
                    $error = $verifyStmt->error;
                    $verifyStmt->close();

                    throw new RuntimeException($error);
                }

                $verifiedAccount = $verifyStmt
                    ->get_result()
                    ->fetch_assoc();

                $verifyStmt->close();

                if (
                    !$verifiedAccount ||
                    strcasecmp(
                        trim((string) $verifiedAccount['status']),
                        'Offline'
                    ) !== 0
                ) {
                    throw new RuntimeException(
                        'Account status did not change to Offline.'
                    );
                }

                // --Commit Database--
                $conn->commit();

                // --Remove Current Ownership--
                rhuOwnerRemoveLocked(
                    $path,
                    $record,
                    $ownershipToken
                );

                $logoutResult = 'success';


            } catch (Throwable $error) {
                try {
                    $conn->rollback();
                } catch (Throwable $rollbackError) {
                    error_log(
                        '[RHU-Q Logout Rollback] ' .
                        $rollbackError->getMessage()
                    );
                }

                throw $error;
            }
        }
    );

} catch (Throwable $error) {
    error_log(
        '[RHU-Q Logout] ' .
        $error->getMessage()
    );

    $logoutResult = 'error';
}

// --Handle Server Failure--
if ($logoutResult === 'error') {
    // Preserve the session so the user can
    // retry logout. Never claim success.
    http_response_code(503);

    header(
        'Content-Type: text/plain; charset=utf-8'
    );

    exit(
        'Logout temporarily unavailable. ' .
        'Please try again.'
    );
}

// --Destroy Current PHP Session--
clearLogoutSession();

// --Redirect--
if ($logoutResult === 'success') {
    logoutRedirect(
        'Logged out successfully.',
        'success'
    );
}

// --Old Or Replaced Session--
logoutRedirect(
    'Your previous session has ended.',
    'info'
);
?>
