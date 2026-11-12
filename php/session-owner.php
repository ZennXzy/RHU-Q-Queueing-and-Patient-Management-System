
<?php
// --Session Ownership Configuration--
// Set this to a persistent, writable directory
// OUTSIDE your public website directory.
//
// Example only: replace with your actual private path.
if (!defined('RHU_SESSION_OWNER_DIR')) {
    define(
        'RHU_SESSION_OWNER_DIR',
        'C:/xampp/rhuq-private/session-owners'
    );
}

// --Configuration--
if (!defined('RHU_SESSION_STALE_SECONDS')) {
    define('RHU_SESSION_STALE_SECONDS', 900);
}

// --Validate Account--
function rhuOwnerAccountKey($type, $id) {
    if (!in_array($type, ['admin', 'patient'], true)) {
        throw new InvalidArgumentException(
            'Invalid account type.'
        );
    }

    $id = filter_var(
        $id,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false || $id === null) {
        throw new InvalidArgumentException(
            'Invalid account ID.'
        );
    }

    return $type . '_' . $id;
}

// --Private Storage--
function rhuOwnerDirectory() {
    $configured = RHU_SESSION_OWNER_DIR;

    if ($configured === '' || !is_dir($configured)) {
        throw new RuntimeException(
            'Private session storage is not configured.'
        );
    }

    $directory = realpath($configured);
    $documentRoot = realpath(
        $_SERVER['DOCUMENT_ROOT'] ?? ''
    );

    if ($directory === false) {
        throw new RuntimeException(
            'Session storage path is invalid.'
        );
    }

    if ($documentRoot !== false) {
        $root = rtrim(
            str_replace('\\', '/', $documentRoot),
            '/'
        );

        $path = str_replace('\\', '/', $directory);

        if (
            $path === $root ||
            str_starts_with($path, $root . '/')
        ) {
            throw new RuntimeException(
                'Session storage must be outside the public directory.'
            );
        }
    }

    if (!is_writable($directory)) {
        throw new RuntimeException(
            'Session storage is not writable.'
        );
    }

    return $directory;
}

// --Account File Paths--
function rhuOwnerPaths($type, $id) {
    $key = rhuOwnerAccountKey($type, $id);

    $hash = hash('sha256', $key);
    $base = rhuOwnerDirectory() . DIRECTORY_SEPARATOR . $hash;

    return [
        'data' => $base . '.json',
        'lock' => $base . '.lock'
    ];
}

// --Generate Ownership Token--
function rhuOwnerNewToken() {
    return bin2hex(random_bytes(32));
}

// --Read Ownership Record--
// Call only while holding the account lock.
function rhuOwnerReadLocked($path) {
    if (!file_exists($path)) {
        return null;
    }

    if (is_link($path)) {
        throw new RuntimeException(
            'Invalid ownership file.'
        );
    }

    $contents = file_get_contents($path);

    if ($contents === false) {
        throw new RuntimeException(
            'Unable to read ownership record.'
        );
    }

    $record = json_decode($contents, true);

    if (
        !is_array($record) ||
        !isset(
            $record['token_hash'],
            $record['last_seen']
        ) ||
        !is_string($record['token_hash']) ||
        !is_numeric($record['last_seen'])
    ) {
        throw new RuntimeException(
            'Ownership record is corrupted.'
        );
    }

    return $record;
}

// --Write Ownership Record--
// Call only while holding the account lock.
function rhuOwnerWriteLocked($path, array $record) {
    $json = json_encode(
        $record,
        JSON_THROW_ON_ERROR
    );

    $temp = tempnam(dirname($path), 'rhu_owner_');

    if ($temp === false) {
        throw new RuntimeException(
            'Unable to create ownership file.'
        );
    }

    try {
        @chmod($temp, 0600);

        if (
            file_put_contents(
                $temp,
                $json,
                LOCK_EX
            ) === false
        ) {
            throw new RuntimeException(
                'Unable to write ownership record.'
            );
        }

        if (!rename($temp, $path)) {
            throw new RuntimeException(
                'Unable to replace ownership record.'
            );
        }

    } finally {
        if (file_exists($temp)) {
            unlink($temp);
        }
    }
}

// --Execute With Account Lock--
// All account ownership operations must use
// this function to serialize access.
function rhuOwnerWithLock($type, $id, callable $callback) {
    $paths = rhuOwnerPaths($type, $id);

    $handle = fopen($paths['lock'], 'c');

    if ($handle === false) {
        throw new RuntimeException(
            'Unable to open ownership lock.'
        );
    }

    try {
        @chmod($paths['lock'], 0600);

        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException(
                'Unable to acquire ownership lock.'
            );
        }

        try {
            $record = rhuOwnerReadLocked(
                $paths['data']
            );

            return $callback(
                $record,
                $paths['data']
            );

        } finally {
            flock($handle, LOCK_UN);
        }

    } finally {
        fclose($handle);
    }
}

// --Create Ownership Record--
// Must be called inside rhuOwnerWithLock.
// Login will use this after validating credentials
// and coordinating the database status update.
function rhuOwnerCreateLocked($path, $token) {
    $now = time();

    $record = [
        'token_hash' => hash('sha256', $token),
        'created_at' => $now,
        'last_seen' => $now
    ];

    rhuOwnerWriteLocked($path, $record);

    return $record;
}

// --Check Ownership--
function rhuOwnerMatches($record, $token) {
    if (
        !is_array($record) ||
        !is_string($token) ||
        $token === ''
    ) {
        return false;
    }

    return hash_equals(
        $record['token_hash'],
        hash('sha256', $token)
    );
}

// --Check Staleness--
function rhuOwnerIsStale($record) {
    if ($record === null) {
        return true;
    }

    return (
        time() - (int) $record['last_seen']
    ) > RHU_SESSION_STALE_SECONDS;
}

// --Refresh Ownership--
// Must be called inside the account lock,
// after verifying token ownership.
function rhuOwnerRefreshLocked(
    $path,
    $record,
    $token
) {
    if (!rhuOwnerMatches($record, $token)) {
        return false;
    }

    $record['last_seen'] = time();

    rhuOwnerWriteLocked($path, $record);

    return true;
}

// --Remove Ownership--
// Must be called inside the account lock.
// Never remove a record belonging to a newer login.
function rhuOwnerRemoveLocked(
    $path,
    $record,
    $token
) {
    if (!rhuOwnerMatches($record, $token)) {
        return false;
    }

    if (file_exists($path) && !unlink($path)) {
        throw new RuntimeException(
            'Unable to remove ownership record.'
        );
    }

    return true;
}
?>
