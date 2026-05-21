export async function registerMedicationPushServiceWorker(): Promise<ServiceWorkerRegistration | null> {
    if (!('serviceWorker' in navigator)) {
        return null;
    }

    const registration = await navigator.serviceWorker.register('/sw.js', {
        scope: '/',
        updateViaCache: 'none',
    });

    await registration.update();

    if (registration.waiting !== null) {
        registration.waiting.postMessage({ type: 'SKIP_WAITING' });
    }

    return registration;
}

export function listenForMedicationPushServiceWorkerUpdates(): void {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    let isReloading = false;

    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (isReloading) {
            return;
        }

        isReloading = true;
        globalThis.location.reload();
    });
}
