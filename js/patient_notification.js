
document.addEventListener("DOMContentLoaded", () => {
    const notificationToggle = document.getElementById("enablePatientNotificationsBtn");
    const notificationStatusText = document.getElementById("notificationStatusText");
    const storageKey = "rhuqPatientNotificationsEnabled";

    let notificationsEnabled = localStorage.getItem(storageKey) === "true";
    let notificationWorker = null;
    let lastKnownStatus = null;
    let lastQueueId = null;
    let isCheckingQueue = false;

    const supported = "Notification" in window && "serviceWorker" in navigator && window.isSecureContext;

    // --Toggle UI--
    function updateToggleUI() {
        const enabled = supported && notificationsEnabled && Notification.permission === "granted";

        if (notificationToggle) {
            notificationToggle.checked = enabled;
            notificationToggle.disabled = !supported;
        }

        if (notificationStatusText) {
            notificationStatusText.textContent = !supported
                ? "Notifications unavailable"
                : Notification.permission === "denied"
                    ? "Blocked by browser settings"
                    : enabled ? "Notifications enabled" : "Notifications disabled";
        }
    }

    // --Service Worker--
    async function registerNotificationWorker() {
        if (!supported) return null;
        if (notificationWorker) return notificationWorker;

        try {
            notificationWorker = await navigator.serviceWorker.register("../patient-notification-sw.js");
            return notificationWorker;
        } catch (error) {
            console.error("Service worker error:", error);
            return null;
        }
    }

    // --Show Notification--
    async function showQueueNotification(status, service) {
        if (!notificationsEnabled || !supported || Notification.permission !== "granted") return false;

        const worker = await registerNotificationWorker();
        if (!worker || !notificationsEnabled) return false;

        try {
            await worker.showNotification("RHU-Q Queue Alert", {
                body: `Your queue status for ${service} is now: ${status}!`,
                icon: "../images/rhu_logo_topNav.png",
                badge: "../images/rhu_logo_topNav.png",
                tag: "rhuq-patient-queue",
                renotify: true,
                data: { url: window.location.href }
            });
            return true;
        } catch (error) {
            console.error("Notification error:", error);
            return false;
        }
    }

    // --Queue Checker--
    async function checkQueueStatus(forceNotification = false) {
        if (isCheckingQueue) return;
        isCheckingQueue = true;

        try {
            const response = await fetch("../php/patient_notification.php", {
                method: "GET",
                credentials: "same-origin",
                cache: "no-store"
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();

            if (data.status !== "success" || !data.hasQueue || !data.queue) {
                lastKnownStatus = null;
                lastQueueId = null;
                return;
            }

            const queue = data.queue;
            const status = String(queue.status || "");
            const service = queue.service_name || "your service";
            const queueId = String(queue.queue_id ?? queue.id ?? `${service}:${queue.created_at}`);

            const isPriority = ["Next in Line", "Serving", "In Progress"].includes(status);
            const hasChanged = queueId !== lastQueueId || status !== lastKnownStatus;

            if (notificationsEnabled && isPriority && (forceNotification || hasChanged)) {
                await showQueueNotification(status, service);
            }

            lastQueueId = queueId;
            lastKnownStatus = status;
        } catch (error) {
            console.error("Queue polling error:", error);
        } finally {
            isCheckingQueue = false;
        }
    }

    // --Enable Notifications--
    async function enableNotifications() {
        if (!supported) {
            updateToggleUI();
            return;
        }

        try {
            let permission = Notification.permission;
            if (permission === "default") permission = await Notification.requestPermission();

            if (permission !== "granted") {
                notificationsEnabled = false;
                localStorage.setItem(storageKey, "false");
                updateToggleUI();
                return;
            }

            const worker = await registerNotificationWorker();

            if (!worker) {
                notificationsEnabled = false;
                localStorage.setItem(storageKey, "false");
                updateToggleUI();
                return;
            }

            notificationsEnabled = true;
            localStorage.setItem(storageKey, "true");
            updateToggleUI();
            await checkQueueStatus(true);
        } catch (error) {
            console.error("Enable notification error:", error);
            notificationsEnabled = false;
            localStorage.setItem(storageKey, "false");
            updateToggleUI();
        }
    }

    // --Disable Notifications--
    async function disableNotifications() {
        notificationsEnabled = false;
        localStorage.setItem(storageKey, "false");
        updateToggleUI();

        try {
            const worker = notificationWorker || await navigator.serviceWorker.getRegistration();
            if (!worker) return;

            const notifications = await worker.getNotifications({ tag: "rhuq-patient-queue" });
            notifications.forEach(notification => notification.close());
        } catch (error) {
            console.error("Disable notification error:", error);
        }
    }

    // --Toggle Event--
    if (notificationToggle) {
        notificationToggle.addEventListener("change", async () => {
            if (notificationToggle.checked) {
                await enableNotifications();
            } else {
                await disableNotifications();
            }
        });
    }

    // --Initialize--
    updateToggleUI();
    registerNotificationWorker();
    checkQueueStatus();

    setInterval(checkQueueStatus, 8000);
    window.addEventListener("focus", updateToggleUI);

    // --Public Functions--
    window.enablePatientNotifications = enableNotifications;
    window.disablePatientNotifications = disableNotifications;
});
