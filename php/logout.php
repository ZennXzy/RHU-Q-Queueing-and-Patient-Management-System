<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Manila');

require_once __DIR__ . '/session-owner.php';
require_once __DIR__ . '/connection.php';

const LOGOUT_REDIRECT = '../pages/login_page.php';

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

function logoutRedirect(string $message, string $type = 'info'): void {
    $params = http_build_query([
        'msg' => $message,
        'type' => $type
    ]);

    header('Location: ' . LOGOUT_REDIRECT . '?' . $params);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin !== '') {
    $parts = parse_url($origin);
    $originHost = $parts['host'] ?? '';
    $requestHost = $_SERVER['HTTP_HOST'] ?? '';

    $expectedScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $originScheme = $parts['scheme'] ?? '';

    if (
        !is_string($originHost) ||
        !is_string($originScheme) ||
        strcasecmp($originHost, parse_url('http://' . $requestHost, PHP_URL_HOST) ?: '') !== 0 ||
        $originScheme !== $expectedScheme
    ) {
        http_response_code(403);
        exit('Invalid request origin.');
    }
} else {
    $fetchSite = $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '';

    if ($fetchSite !== '' && !in_array($fetchSite, ['same-origin', 'none'], true)) {
        http_response_code(403);
        exit('Cross-origin logout rejected.');
    }
}

$userId = filter_var(
    $_SESSION['logged_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$username = $_SESSION['logged_name'] ?? null;
$userType = strtolower(trim((string) ($_SESSION['user_type'] ?? '')));
$ownershipToken = $_SESSION['ownership_token'] ?? null;

$validSession = (
    $userId !== false &&
    $userId !== null &&
    is_string($username) &&
    trim($username) !== '' &&
    in_array($userType, ['admin', 'patient'], true) &&
    is_string($ownershipToken) &&
    preg_match('/^[a-f0-9]{64}$/D', $ownershipToken) === 1
);

if (!$validSession) {
    clearLogoutSession();
    logoutRedirect('Your session has ended.');
}

if ($userType === 'admin') {
    $table = 'admin_auth';
    $idColumn = 'id';
} else {
    $table = 'patient_auth';
    $idColumn = 'patient_auth_id';
}

$logoutResult = 'error';

try {
    if (!isset($conn) || $conn->connect_errno) {
        throw new RuntimeException('Database connection unavailable.');
    }

    if (!$conn->query("SET time_zone = '+08:00'")) {
        throw new RuntimeException('Database timezone unavailable.');
    }

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
            if ($record === null || !rhuOwnerMatches($record, $ownershipToken)) {
                $logoutResult = 'not_owner';
                return;
            }

            $conn->begin_transaction();

            try {
                $selectSql = "
                    SELECT status
                    FROM {$table}
                    WHERE {$idColumn} = ?
                      AND username = ?
                    LIMIT 1
                    FOR UPDATE
                ";

                $stmt = $conn->prepare($selectSql);
                if (!$stmt) {
                    throw new RuntimeException('Unable to prepare account lookup.');
                }

                $stmt->bind_param('is', $userId, $username);
                if (!$stmt->execute()) {
                    $error = $stmt->error;
                    $stmt->close();
                    throw new RuntimeException($error);
                }

                $account = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$account) {
                    $conn->rollback();
                    $logoutResult = 'not_owner';
                    return;
                }

                if ($userType === 'admin') {
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
                    throw new RuntimeException('Unable to prepare logout update.');
                }

                $updateStmt->bind_param('is', $userId, $username);
                if (!$updateStmt->execute()) {
                    $error = $updateStmt->error;
                    $updateStmt->close();
                    throw new RuntimeException($error);
                }
                $updateStmt->close();

                if ($userType === 'admin') {
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
                    throw new RuntimeException('Unable to prepare logout verification.');
                }

                $verifyStmt->bind_param('is', $userId, $username);
                if (!$verifyStmt->execute()) {
                    $error = $verifyStmt->error;
                    $verifyStmt->close();
                    throw new RuntimeException($error);
                }

                $verifiedAccount = $verifyStmt->get_result()->fetch_assoc();
                $verifyStmt->close();

                if (
                    !$verifiedAccount ||
                    strcasecmp(trim((string) $verifiedAccount['status']), 'Offline') !== 0
                ) {
                    throw new RuntimeException('Account status did not change to Offline.');
                }

                $conn->commit();

                rhuOwnerRemoveLocked($path, $record, $ownershipToken);
                $logoutResult = 'success';

            } catch (Throwable $error) {
                try {
                    $conn->rollback();
                } catch (Throwable $rollbackError) {
                    error_log('[RHU-Q Logout Rollback] ' . $rollbackError->getMessage());
                }
                throw $error;
            }
        }
    );

} catch (Throwable $error) {
    error_log('[RHU-Q Logout] ' . $error->getMessage());
    $logoutResult = 'error';
}

if ($logoutResult === 'error') {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Logout temporarily unavailable. Please try again.');
}

// Logout Audit Logging
// Audit failures must never prevent a completed logout.
if ($logoutResult === 'success') {
    try {
        $roleType = ucfirst($userType);
        $logMessage = 'Logged out.';
        $logStmt = $conn->prepare("
            INSERT INTO logs (user_id, user_type, log_message)
            VALUES (?, ?, ?)
        ");

        if (!$logStmt) {
            throw new RuntimeException('Unable to prepare logout audit log.');
        }

        try {
            $logStmt->bind_param('iss', $userId, $roleType, $logMessage);
            if (!$logStmt->execute()) {
                throw new RuntimeException('Unable to save logout audit log: ' . $logStmt->error);
            }
        } finally {
            $logStmt->close();
        }
    } catch (Throwable $auditError) {
        error_log('[RHU-Q Logout Audit] ' . $auditError->getMessage());
    }
}

clearLogoutSession();

if ($logoutResult === 'success') {
    logoutRedirect('Logged out successfully.', 'success');
}

logoutRedirect('Your previous session has ended.', 'info');
?>