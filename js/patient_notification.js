document.addEventListener('DOMContentLoaded', () => {
    // 1. Request browser desktop notification permission on page load
    if (window.Notification && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    let lastKnownStatus = null;

    // 2. Poll background endpoint to check for status changes
    const checkPatientNotificationStatus = async () => {
        try {
            const response = await fetch('../php/patient_notification.php', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.status === 'success' && data.hasQueue && data.queue) {
                const q = data.queue;
                const currentStatus = q.status;

                const isInitialCheck = (lastKnownStatus === null);
                const hasStatusChanged = (lastKnownStatus !== null && lastKnownStatus !== currentStatus);
                const isPriorityStatus = currentStatus.includes('Next') || currentStatus.includes('Serving') || currentStatus.includes('In Progress');

                if (hasStatusChanged && isPriorityStatus) {
                    // Alert on transition (e.g., moved from "Waiting (1 ahead)" -> "Next in Line")
                    fireDeviceNotification(currentStatus, q.service_name);
                } else if (isInitialCheck && isPriorityStatus) {
                    // Alert immediately on first poll if there is 0 patient ahead
                    fireDeviceNotification(currentStatus, q.service_name);
                }

                lastKnownStatus = currentStatus;
            } else {
                lastKnownStatus = null;
            }
        } catch (err) {
            console.error('Patient notification poll error:', err);
        }
    };

    // 3. Trigger native browser popup notification
    function fireDeviceNotification(statusText, serviceName) {
        if (window.Notification && Notification.permission === 'granted') {
            new Notification("RHU-Q Queue Alert", {
                body: `Your queue status for ${serviceName} is now: ${statusText}!`,
                icon: "../images/rhu_logo_topNav.png"
            });
        }
    }

    // Run initial check on load
    checkPatientNotificationStatus();

    // Poll every 12 seconds in the background
    setInterval(checkPatientNotificationStatus, 8000);
});