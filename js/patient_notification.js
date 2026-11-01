
document.addEventListener("DOMContentLoaded", () => {
    let lastKnownStatus = null;
    let lastQueueId = null;
    let notificationWorker = null;

    // --Service Worker--

    async function setupNotifications() {
        if (!("Notification" in window)) {
            console.warn("Notifications are not supported.");
            return;
        }

        if (!("serviceWorker" in navigator)) {
            console.warn("Service workers are not supported.");
            return;
        }

        if (!window.isSecureContext) {
            console.warn("Notifications require HTTPS or localhost.");
            return;
        }

        try {
            notificationWorker = await navigator.serviceWorker.register(
                "../patient-notification-sw.js"
            );

            console.log("Notification service worker registered.");
        } catch (error) {
            console.error("Service worker registration failed:", error);
        }
    }

    // --Notification Permission--

    async function requestPatientNotificationPermission() {
        if (!("Notification" in window)) {
            return false;
        }

        if (Notification.permission === "granted") {
            return true;
        }

        if (Notification.permission === "denied") {
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            return permission === "granted";
        } catch (error) {
            console.error("Notification permission error:", error);
            return false;
        }
    }

    // --Device Notification--

    async function fireDeviceNotification(statusText, serviceName) {
        if (!("Notification" in window)) return;
        if (Notification.permission !== "granted") return;

        const title = "RHU-Q Queue Alert";
        const options = {
            body: `Your queue status for ${serviceName} is now: ${statusText}!`,
            icon: "/images/rhu_logo_topNav.png",
            badge: "/images/rhu_logo_topNav.png",
            tag: "rhuq-patient-queue",
            renotify: true,
            data: {
                url: window.location.href
            }
        };

        try {
            if ("serviceWorker" in navigator && window.isSecureContext) {
                const registration =
                    notificationWorker ||
                    await navigator.serviceWorker.ready;

                await registration.showNotification(title, options);
                return;
            }

            // --Desktop Fallback--

            if ("Notification" in window) {
                new Notification(title, options);
            }
        } catch (error) {
            console.error("Unable to show notification:", error);
        }
    }

    // --Queue Status Checker--

    async function checkPatientNotificationStatus() {
        try {
            const response = await fetch(
                "../php/patient_notification.php",
                {
                    method: "GET",
                    credentials: "same-origin",
                    cache: "no-store",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (
                data.status !== "success" ||
                !data.hasQueue ||
                !data.queue
            ) {
                lastKnownStatus = null;
                lastQueueId = null;
                return;
            }

            const queue = data.queue;
            const currentStatus = String(queue.status || "");
            const currentQueueId = String(
                queue.queue_id ??
                queue.id ??
                `${queue.service_name}:${queue.created_at}`
            );

            const isNewQueue = currentQueueId !== lastQueueId;
            const hasStatusChanged =
                lastKnownStatus !== currentStatus;

            const isPriorityStatus =
                currentStatus === "Next in Line" ||
                currentStatus === "Serving" ||
                currentStatus === "In Progress";

            if (
                isPriorityStatus &&
                (isNewQueue || hasStatusChanged)
            ) {
                await fireDeviceNotification(
                    currentStatus,
                    queue.service_name || "your service"
                );
            }

            lastQueueId = currentQueueId;
            lastKnownStatus = currentStatus;

        } catch (error) {
            console.error("Patient notification poll error:", error);
        }
    }

    // --Initialize--

    setupNotifications();

    // Permission must be requested by a user action.
    // Connect this function to an Enable Notifications button.
    window.enablePatientNotifications =
        requestPatientNotificationPermission;

    checkPatientNotificationStatus();

    setInterval(checkPatientNotificationStatus, 8000);
});
