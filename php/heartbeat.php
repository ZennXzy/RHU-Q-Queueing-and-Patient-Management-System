
<?php
// --Response Headers--
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

date_default_timezone_set('Asia/Manila');


// --Database Connection--
require_once __DIR__ . '/connection.php';
// --Dependencies--
require_once __DIR__ . '/session-owner.php';

// --JSON Response--
function heartbeatResponse(
    int $code,
    string $status,
    string $message
): void {
    http_response_code($code);

    echo json_encode([
        'status' => $status,
        'message' => $message,
        'server_time' => date('Y-m-d H:i:s')
    ]);

    exit();
}

// --Validate Request Method--
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');

    heartbeatResponse(
        405,
        'error',
        'Method not allowed.'
    );
}

// --Validate Request Origin--
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin !== '') {
    $originParts = parse_url($origin);

    $requestHost = $_SERVER['HTTP_HOST'] ?? '';

    $expectedHost = parse_url(
        'http://' . $requestHost,
        PHP_URL_HOST
    );

    $originHost = $originParts['host'] ?? null;
    $originScheme = $originParts['scheme'] ?? '';

    $isHttps = (
        !empty($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] !== 'off'
    );

    $expectedScheme = $isHttps ? 'https' : 'http';

    if (
        !is_string($originHost) ||
        !is_string($expectedHost) ||
        strcasecmp($originHost, $expectedHost) !== 0 ||
        $originScheme !== $expectedScheme
    ) {
        heartbeatResponse(
            403,
            'forbidden',
            'Invalid request origin.'
        );
    }
}

// --Start PHP Session--
if (session_status() === PHP_SESSION_NONE) {
    if (!session_start()) {
        heartbeatResponse(
            503,
            'error',
            'Unable to start session.'
        );
    }
}

// --Read Session Data--
$userId = filter_var(
    $_SESSION['logged_id'] ?? null,
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1
        ]
    ]
);

$username = $_SESSION['logged_name'] ?? null;

$userType = strtolower(
    trim((string) ($_SESSION['user_type'] ?? ''))
);

$ownershipToken =
    $_SESSION['ownership_token'] ?? null;

// --Validate Authentication--
if (
    $userId === false ||
    $userId === null ||
    !is_string($username) ||
    trim($username) === '' ||
    !in_array($userType, ['admin', 'patient'], true)
) {
    heartbeatResponse(
        401,
        'unauthenticated',
        'No valid login session.'
    );
}

// --Validate Ownership Token--
if (
    !is_string($ownershipToken) ||
    !preg_match('/^[a-f0-9]{64}$/D', $ownershipToken)
) {
    heartbeatResponse(
        401,
        'unauthenticated',
        'Session ownership token is missing or invalid.'
    );
}


if (!isset($conn) || $conn->connect_errno) {
    heartbeatResponse(
        503,
        'error',
        'Database connection unavailable.'
    );
}

// --Database Timezone--
if (!$conn->query("SET time_zone = '+08:00'")) {
    error_log(
        'Heartbeat timezone error: ' . $conn->error
    );

    heartbeatResponse(
        503,
        'error',
        'Database timezone unavailable.'
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

// --Heartbeat State--
$heartbeatStatus = 'error';
$heartbeatMessage = 'Unable to update heartbeat.';
$heartbeatCode = 503;

// --Verify And Refresh Ownership--
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
            &$heartbeatStatus,
            &$heartbeatMessage,
            &$heartbeatCode
        ) {

            // --Check Ownership Record--
            if ($record === null) {
                $heartbeatStatus = 'unauthenticated';
                $heartbeatMessage =
                    'Session ownership record not found.';
                $heartbeatCode = 401;
                return;
            }

            // --Verify Current Owner--
            if (!rhuOwnerMatches(
                $record,
                $ownershipToken
            )) {
                $heartbeatStatus = 'logged_out';
                $heartbeatMessage =
                    'This session has been replaced by another login.';
                $heartbeatCode = 401;
                return;
            }

            // --Update Database Heartbeat--
            $transaction_date = date('Y-m-d H:i:s');
            $sql = "
                UPDATE {$table}
                SET last_active = ?
                WHERE {$idColumn} = ?
                  AND username = ?
                  AND status = 'Online'
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new RuntimeException(
                    'Unable to prepare heartbeat update.'
                );
            }

            $stmt->bind_param(
                'sis',
                $transaction_date,
                $userId,
                $username
            );

            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();

                throw new RuntimeException(
                    'Heartbeat update failed: ' . $error
                );
            }

            $stmt->close();

            $checkSql = "
                SELECT status
                FROM {$table}
                WHERE {$idColumn} = ?
                  AND username = ?
                LIMIT 1
            ";

            $checkStmt = $conn->prepare(
                $checkSql
            );

            if (!$checkStmt) {
                throw new RuntimeException(
                    'Unable to prepare account verification.'
                );
            }

            $checkStmt->bind_param(
                'is',
                $userId,
                $username
            );

            if (!$checkStmt->execute()) {
                $error = $checkStmt->error;
                $checkStmt->close();

                throw new RuntimeException(
                    'Account verification failed: ' . $error
                );
            }

            $account = $checkStmt
                ->get_result()
                ->fetch_assoc();

            $checkStmt->close();

            // --Account Missing--
            if (!$account) {
                $heartbeatStatus = 'unauthenticated';
                $heartbeatMessage =
                    'Account no longer exists.';
                $heartbeatCode = 401;
                return;
            }

            // --Account Offline--
            if (
                strcasecmp(
                    trim((string) ($account['status'] ?? '')),
                    'Online'
                ) !== 0
            ) {
                $heartbeatStatus = 'logged_out';
                $heartbeatMessage =
                    'Account is no longer online.';
                $heartbeatCode = 401;
                return;
            }

            // --Refresh Ownership Timestamp--
            if (!rhuOwnerRefreshLocked(
                $path,
                $record,
                $ownershipToken
            )) {
                throw new RuntimeException(
                    'Unable to refresh session ownership.'
                );
            }

            // --Heartbeat Successful--
            $heartbeatStatus = 'active';
            $heartbeatMessage =
                'Heartbeat updated successfully.';
            $heartbeatCode = 200;
        }
    );

} catch (Throwable $error) {

    error_log(
        '[RHU-Q Heartbeat] ' . $error->getMessage()
    );

    heartbeatResponse(
        503,
        'error',
        'Session verification temporarily unavailable.'
    );
}

// --Return Response--
heartbeatResponse(
    $heartbeatCode,
    $heartbeatStatus,
    $heartbeatMessage
);
?>
