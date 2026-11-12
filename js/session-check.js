
(() => {
  // --Prevent Duplicate Initialization--
  if (window.__rhuSessionMonitorInitialized) return;
  window.__rhuSessionMonitorInitialized = true;

  // --Configuration--
  const SESSION_CHECK_INTERVAL = 5000;
  const HEARTBEAT_INTERVAL = 30000;
  const REQUEST_TIMEOUT = 10000;

  const SESSION_URL = "../api/check-private.php?ajax_check=1";
  const HEARTBEAT_URL = "../php/heartbeat.php";
  const LOGIN_URL = "../pages/login_page.php";

  // --Session State--
  let sessionChecking = false;
  let heartbeatChecking = false;
  let redirecting = false;
  let lastHeartbeat = 0;

  let sessionTimer = null;
  let heartbeatTimer = null;

  // --Redirect to Login--
  function redirectToLogin(reason = "Session Expired") {
    if (redirecting) return;

    redirecting = true;

    clearInterval(sessionTimer);
    clearInterval(heartbeatTimer);

    const params = new URLSearchParams({
      msg: reason,
      type: "info"
    });

    window.location.replace(
      `${LOGIN_URL}?${params.toString()}`
    );
  }

  // --Fetch With Timeout--
  async function fetchWithTimeout(url, options = {}) {
    const controller = new AbortController();

    const timeout = setTimeout(() => {
      controller.abort();
    }, REQUEST_TIMEOUT);

    try {
      return await fetch(url, {
        ...options,
        credentials: "same-origin",
        cache: "no-store",
        signal: controller.signal
      });
    } finally {
      clearTimeout(timeout);
    }
  }

  // --Read JSON Response--
  async function readJSON(response) {
    const contentType =
      response.headers.get("content-type") || "";

    if (!contentType.includes("application/json")) {
      return null;
    }

    try {
      return await response.json();
    } catch (error) {
      console.warn(
        "[RHU-Q] Invalid JSON response:",
        error.message
      );

      return null;
    }
  }

  // --Validate Session--
  async function checkSessionStatus() {
    if (sessionChecking || redirecting) return;

    sessionChecking = true;

    try {
      const response = await fetchWithTimeout(
        SESSION_URL,
        {
          method: "GET"
        }
      );

      const data = await readJSON(response);

      // --Confirmed Logout--
      if (
        data &&
        (
          data.status === "logged_out" ||
          data.status === "unauthenticated"
        )
      ) {
        const reason =
          data.reason === "Clinic closed"
            ? "System closed for the day. Logged out."
            : "Session Expired";

        redirectToLogin(reason);
        return;
      }

      // --Server or Network Error--
      if (!response.ok) {
        console.warn(
          "[RHU-Q] Session check HTTP:",
          response.status
        );

        return;
      }

      if (!data) {
        console.warn(
          "[RHU-Q] Session check returned no valid JSON."
        );
      }

    } catch (error) {
      console.warn(
        "[RHU-Q] Session check unavailable:",
        error.message
      );

    } finally {
      sessionChecking = false;
    }
  }

  // --Send Heartbeat--
  async function sendHeartbeat() {
    if (heartbeatChecking || redirecting) return;

    heartbeatChecking = true;

    try {
      const response = await fetchWithTimeout(
        HEARTBEAT_URL,
        {
          method: "POST",
          headers: {
            "Accept": "application/json"
          }
        }
      );

      const data = await readJSON(response);

      // --Confirmed Invalid Session--
      if (
        response.status === 401 &&
        data &&
        (
          data.status === "unauthenticated" ||
          data.status === "logged_out"
        )
      ) {
        redirectToLogin();
        return;
      }

      // --Heartbeat Error--
      if (!response.ok) {
        console.warn(
          "[RHU-Q] Heartbeat HTTP:",
          response.status,
          data?.message || ""
        );

        return;
      }

      // --Heartbeat Success--
      if (data?.status === "active") {
        lastHeartbeat = Date.now();

        console.debug(
          "[RHU-Q] Heartbeat successful:",
          data.server_time
        );
      } else {
        console.warn(
          "[RHU-Q] Unexpected heartbeat response:",
          data
        );
      }

    } catch (error) {
      console.warn(
        "[RHU-Q] Heartbeat unavailable:",
        error.message
      );

    } finally {
      heartbeatChecking = false;
    }
  }

  // --Refresh Connection--
  function refreshConnection() {
    if (redirecting) return;

    checkSessionStatus();

    if (
      Date.now() - lastHeartbeat >=
      HEARTBEAT_INTERVAL
    ) {
      sendHeartbeat();
    }
  }

  // --Initialize Monitor--
  function initializeSessionMonitor() {
    // --Initial Requests--
    checkSessionStatus();
    sendHeartbeat();

    // --Session Verification--
    sessionTimer = setInterval(() => {
      if (navigator.onLine) {
        checkSessionStatus();
      }
    }, SESSION_CHECK_INTERVAL);

    // --Heartbeat--
    heartbeatTimer = setInterval(() => {
      if (navigator.onLine) {
        sendHeartbeat();
      }
    }, HEARTBEAT_INTERVAL);

    // --Tab Visibility--
    document.addEventListener(
      "visibilitychange",
      () => {
        if (document.visibilityState === "visible") {
          refreshConnection();
        }
      }
    );

    // --Connection Restored--
    window.addEventListener(
      "online",
      refreshConnection
    );

    // --Page Restored--
    window.addEventListener("pageshow", (event) => {
      if (event.persisted) {
        refreshConnection();
      }
    });

    console.info(
      "[RHU-Q] Session monitor initialized."
    );
  }

  // --Start Monitor--
  if (document.readyState === "loading") {
    document.addEventListener(
      "DOMContentLoaded",
      initializeSessionMonitor,
      { once: true }
    );
  } else {
    initializeSessionMonitor();
  }
})();
