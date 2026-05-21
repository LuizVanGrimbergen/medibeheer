self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

self.addEventListener('push', (event) => {
    if (!self.Notification || self.Notification.permission !== 'granted') {
        return;
    }

    if (!event.data) {
        return;
    }

    const payload = event.data.json();

    const title = (payload.title ?? '').trim();
    const body = (payload.body ?? '').trim();

    event.waitUntil(
        self.registration.showNotification(title || body, {
            body: title !== '' && body !== '' ? body : undefined,
            icon: payload.icon ?? '/images/medibeheer-pwa.png',
            tag: payload.tag,
            data: payload.data ?? {},
        }),
    );
});

function notifyOpenPushMarkSuccessPage() {
    try {
        const channel = new BroadcastChannel('medibeheer-medication-push-mark');
        channel.postMessage({ openSuccess: true });
        channel.close();
    } catch {
        return;
    }
}

function buildPushMarkSuccessUrl(data) {
    const successUrl = new URL('/patient/medication-push-mark/success', self.location.origin);

    const medicationName = data.medicationName ?? '';

    if (medicationName !== '') {
        successUrl.searchParams.set('medication', medicationName);
    }

    return successUrl.href;
}

function openAppUrl(url) {
    return self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(async (clientList) => {
        for (const client of clientList) {
            if (!('navigate' in client)) {
                continue;
            }

            try {
                await client.navigate(url);

                return;
            } catch {
                continue;
            }
        }

        if (self.clients.openWindow) {
            await self.clients.openWindow(url);
        }
    });
}

function openPushMarkSuccessPage(data) {
    return openAppUrl(buildPushMarkSuccessUrl(data));
}

function markTakenFromPush(markTakenUrl, data) {
    return fetch(markTakenUrl, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-Push-Mark': '1',
        },
        credentials: 'omit',
        redirect: 'manual',
    }).then((response) => {
        if (response.status !== 204 && response.status !== 200) {
            throw new Error('mark intake failed');
        }

        notifyOpenPushMarkSuccessPage();

        return openPushMarkSuccessPage(data);
    });
}

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const data = event.notification.data ?? {};

    if (data.markTakenUrl) {
        event.waitUntil(markTakenFromPush(data.markTakenUrl, data));

        return;
    }

    if (data.openUrl) {
        event.waitUntil(openAppUrl(data.openUrl));
    }
});
