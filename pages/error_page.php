<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const REDIRECT_SECONDS = 5;

$LOGIN_PAGE = '../index.html';

// Where each role lands after an error: [path from project root, label shown on the page]
$ROLE_HOME = [
    'Admin'        => ['pages/dashboard_page.php',  'Dashboard'],
    'Doctor/Staff' => ['pages/dashboard_page.php',  'Dashboard'],
    'Patient'      => ['pages/queue_display_page.php',  'Queue Display'],
    'Display'      => ['pages/queue_display_page.php',  'Queue Display'],
];

/* ------------------------- Error messages ------------------------- */
// title = what happened, message = what to do about it
$ERRORS = [
    400 => ['Request could not be understood',  'The link or form data was not valid. Go back and try again.'],
    401 => ['Please sign in',                   'Your session has ended or you are not signed in yet. Sign in to continue.'],
    403 => ['Access not allowed',               'Your account role does not have permission to open this page.'],
    404 => ['Page not found',                   'The page may have moved, or the link may be typed wrong. Check the address or use the menu.'],
    405 => ['Action not allowed',               'This page does not accept that kind of request. Go back and use the buttons on the page.'],
    408 => ['Request timed out',                'The connection took too long. Check your internet and try again.'],
    429 => ['Too many requests',                'You sent requests too quickly. Wait a moment, then try again.'],
    500 => ['Something went wrong on our side', 'The system hit an unexpected problem. Try again in a moment. If it keeps happening, tell the RHU administrator.'],
    502 => ['Bad response from the server',     'The server received an invalid reply. Try again in a moment.'],
    503 => ['Service unavailable',              'The system is busy or being maintained. Try again in a few minutes.'],
    504 => ['Server took too long to respond',  'The server did not answer in time. Try again in a moment.'],
];

/* ------------------------- Work out the error code ------------------------- */
$code = (int)($_GET['code'] ?? ($_SERVER['REDIRECT_STATUS'] ?? 404));
if (!isset($ERRORS[$code])) {
    $code = 404;
}
[$title, $message] = $ERRORS[$code];

// Absolute base path of the project (works even when Apache shows this page for a
// URL in a different folder, because SCRIPT_NAME is always this file).
$base = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))), '/');

$role   = $_SESSION['logged_role'] ?? '';
$target = $ROLE_HOME[$role] ?? null;

if ($code === 401 || $target === null) {
    $returnUrl   = $base . '/' . ltrim($LOGIN_PAGE, '/');
    $returnLabel = 'sign-in page';
} else {
    $returnUrl   = $base . '/' . $target[0];
    $returnLabel = $target[1];
}
// The target is decided on the server only; it is never read from the URL.

/* ------------------------- Headers ------------------------- */
if (!headers_sent()) {
    http_response_code($code);
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');
}

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $code ?> <?= $h($title) ?> | RHU-Q</title>
    <link rel="icon" href="<?= $h($base) ?>/images/rhu_logo_topNav.png" type="image/png">
    <link rel="stylesheet" href="<?= $h($base) ?>/styles/error_page.css">

    <!-- Fallback if JavaScript is turned off -->
    <noscript>
        <meta http-equiv="refresh" content="<?= REDIRECT_SECONDS ?>;url=<?= $h($returnUrl) ?>">
    </noscript>
</head>
<body>
    <header class="brand-bar">
        <img src="<?= $h($base) ?>/images/rhu_logo_topNav.png" alt="RHU logo">
        <span>RHU-Q: Queuing and Patient Management System</span>
    </header>

    <main class="error-wrap">
        <section class="error-panel" aria-labelledby="error-title">

            <div class="ring" aria-hidden="true">
                <svg viewBox="0 0 120 120" focusable="false">
                    <circle class="ring-track" cx="60" cy="60" r="54"></circle>
                    <circle class="ring-progress" id="ringProgress" cx="60" cy="60" r="54"></circle>
                </svg>
                <div class="ring-code"><?= $code ?></div>
            </div>

            <h1 id="error-title"><?= $h($title) ?></h1>
            <p class="error-message"><?= $h($message) ?></p>

            <p class="countdown" id="countdownText" role="status">
                Taking you to the <?= $h($returnLabel) ?> in
                <strong id="secondsLeft"><?= REDIRECT_SECONDS ?></strong>
                <span id="secondsUnit">seconds</span>.
            </p>

            <div class="actions">
                <a class="btn btn-primary" id="goNowBtn" href="<?= $h($returnUrl) ?>">Go to <?= $h($returnLabel) ?> now</a>
                <button type="button" class="btn btn-secondary" id="backBtn">Go back one page</button>
                <button type="button" class="btn btn-text" id="stopBtn">Stop countdown</button>
            </div>
        </section>
    </main>

    <script>
        (function () {
            const TOTAL_MS  = <?= REDIRECT_SECONDS ?> * 1000;
            const RETURN_URL = <?= json_encode($returnUrl) ?>;
            const RETURN_LABEL = <?= json_encode($returnLabel) ?>;

            const ring      = document.getElementById('ringProgress');
            const secondsEl = document.getElementById('secondsLeft');
            const unitEl    = document.getElementById('secondsUnit');
            const textEl    = document.getElementById('countdownText');
            const stopBtn   = document.getElementById('stopBtn');
            const backBtn   = document.getElementById('backBtn');

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const CIRC = 2 * Math.PI * 54;
            ring.style.strokeDasharray = CIRC;

            const start = Date.now();
            let rafId = null;
            let redirectTimer = null;
            let stopped = false;

            function render() {
                const remaining = Math.max(0, TOTAL_MS - (Date.now() - start));
                const secs = Math.ceil(remaining / 1000);

                secondsEl.textContent = secs;
                unitEl.textContent = secs === 1 ? 'second' : 'seconds';

                // Smooth ring, or one step per second if the person prefers less motion
                const progress = reduceMotion ? secs / (TOTAL_MS / 1000) : remaining / TOTAL_MS;
                ring.style.strokeDashoffset = CIRC * (1 - progress);

                if (!stopped && remaining > 0) {
                    rafId = requestAnimationFrame(render);
                }
            }

            function goBackToApp() {
                window.location.replace(RETURN_URL);
            }

            function stopCountdown() {
                stopped = true;
                clearTimeout(redirectTimer);
                cancelAnimationFrame(rafId);
                ring.style.strokeDashoffset = 0;
                document.querySelector('.ring').classList.add('paused');
                textEl.textContent = 'Countdown stopped. Use a button below when you are ready.';
                stopBtn.hidden = true;
            }

            redirectTimer = setTimeout(goBackToApp, TOTAL_MS);
            render();

            stopBtn.addEventListener('click', stopCountdown);

            backBtn.addEventListener('click', function () {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    goBackToApp();
                }
            });
        })();
    </script>
</body>
</html>