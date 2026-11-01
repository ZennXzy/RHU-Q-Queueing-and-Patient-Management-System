
self.addEventListener("notificationclick", (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url;

    if (!targetUrl) return;

    event.waitUntil(
        (async () => {
            const clientsList = await clients.matchAll({
                type: "window",
                includeUncontrolled: true
            });

            for (const client of clientsList) {
                if (client.url === targetUrl) {
                    return client.focus();
                }
            }

            return clients.openWindow(targetUrl);
        })()
    );
});
