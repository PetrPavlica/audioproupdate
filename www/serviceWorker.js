self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    const sendNotification = data => {
        // you could refresh a notification badge here with postMessage API
        const title = data.title;
        const options = {
            body: data.body,
            icon: data.icon !== undefined ? data.icon : null,
            image: data.image !== undefined ? data.image : null,
        };

        if (data.url !== undefined) {
            options.data = {
                url: data.url
            }
        }

        return self.registration.showNotification(title, options);
    };

    if (event.data) {
        const message = JSON.parse(event.data.text());
        event.waitUntil(sendNotification(message));
    }
});

self.addEventListener("notificationclick", (event) => {
    event.waitUntil(async function () {
        if (event.notification.data.url) {
            const allClients = await clients.matchAll({
                includeUncontrolled: true
            });
            let webClient;
            let appUrl = event.notification.data.url;
            for (const client of allClients) {
                if (client['url'].indexOf(appUrl) >= 0) {
                    client.focus();
                    webClient = client;
                    break;
                }
            }
            if (!webClient) {
                webClient = await clients.openWindow(appUrl);
            }
        }
    }());
});