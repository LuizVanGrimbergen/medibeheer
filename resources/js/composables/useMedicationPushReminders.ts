import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { i18n } from '@/i18n';
import { registerMedicationPushServiceWorker } from '@/lib/medicationPushServiceWorker';
import type { PageProps } from '@/lib/types';
import { urlBase64ToUint8Array } from '@/lib/webpush/vapidPublicKey';

type PushSubscriptionJson = {
    endpoint: string;
    keys: {
        p256dh: string;
        auth: string;
    };
};

type MedicationPushReminderRole = 'patient' | 'family_member';

type MedicationPushReminderConfig = {
    role: MedicationPushReminderRole;
    storeRoute: string;
    destroyRoute: string;
    dashboardPromptDismissedStorageKey: string;
    translationPrefix: string;
};

const isRegistering = ref(false);
const isUnregistering = ref(false);
const registrationError = ref<string | null>(null);

function readDashboardPromptDismissed(storageKey: string): boolean {
    try {
        return globalThis.localStorage.getItem(storageKey) === '1';
    } catch {
        return false;
    }
}

function readCsrfToken(): string | null {
    const match = /XSRF-TOKEN=([^;]+)/.exec(document.cookie);

    if (match?.[1] === undefined) {
        return null;
    }

    return decodeURIComponent(match[1]);
}

function resolvePushManager(
    registration: ServiceWorkerRegistration,
): PushManager {
    const navigatorWithPush = navigator as Navigator & {
        pushManager?: PushManager;
    };

    if (navigatorWithPush.pushManager != null) {
        return navigatorWithPush.pushManager;
    }

    return registration.pushManager;
}

async function unsubscribeInBrowser(): Promise<void> {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    const registration = await navigator.serviceWorker.getRegistration('/');

    if (registration == null) {
        return;
    }

    const subscription =
        await resolvePushManager(registration).getSubscription();

    if (subscription === null) {
        return;
    }

    await subscription.unsubscribe();
}

async function subscribeWithApplicationServerKey(
    registration: ServiceWorkerRegistration,
    vapidPublicKey: string,
): Promise<PushSubscription> {
    const pushManager = resolvePushManager(registration);
    const applicationServerKey = urlBase64ToUint8Array(
        vapidPublicKey,
    ) as BufferSource;

    try {
        return await pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey,
        });
    } catch (error) {
        const message = error instanceof Error ? error.message : '';

        if (!message.includes('applicationServerKey')) {
            throw error;
        }

        return pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey,
        });
    }
}

function configForRole(
    role: MedicationPushReminderRole,
): MedicationPushReminderConfig {
    if (role === 'family_member') {
        return {
            role,
            storeRoute: 'family.push-subscriptions.store',
            destroyRoute: 'family.push-subscriptions.destroy',
            dashboardPromptDismissedStorageKey:
                'medibeheer:family-medication-reminder-prompt-dismissed',
            translationPrefix: 'family.medicationReminders',
        };
    }

    return {
        role,
        storeRoute: 'patient.push-subscriptions.store',
        destroyRoute: 'patient.push-subscriptions.destroy',
        dashboardPromptDismissedStorageKey:
            'medibeheer:patient-medication-reminder-prompt-dismissed',
        translationPrefix: 'patient.medicationReminders',
    };
}

export function useMedicationPushReminders(
    role: MedicationPushReminderRole = 'patient',
) {
    const config = configForRole(role);
    const page = usePage<PageProps>();

    const publicKey = computed(
        (): string | null => page.props.webpush?.publicKey ?? null,
    );

    const isSubscribedOnServer = computed(
        (): boolean => page.props.webpush?.subscribed ?? false,
    );

    const browserSupportsPush = computed(
        (): boolean =>
            globalThis.Notification !== undefined &&
            'serviceWorker' in navigator &&
            'PushManager' in globalThis,
    );

    const canEnableReminders = computed(
        (): boolean => publicKey.value !== null && browserSupportsPush.value,
    );

    const permission = computed((): NotificationPermission => {
        if (globalThis.Notification === undefined) {
            return 'denied';
        }

        return globalThis.Notification.permission;
    });

    const isPermissionDenied = computed(
        (): boolean => permission.value === 'denied',
    );

    const isDashboardPromptDismissed = ref(
        readDashboardPromptDismissed(config.dashboardPromptDismissedStorageKey),
    );

    const shouldShowCard = computed((): boolean => {
        if (!browserSupportsPush.value || publicKey.value === null) {
            return true;
        }

        if (isPermissionDenied.value) {
            return true;
        }

        return !isSubscribedOnServer.value;
    });

    const shouldShowDashboardPrompt = computed(
        (): boolean =>
            !isDashboardPromptDismissed.value && shouldShowCard.value,
    );

    function dismissDashboardPrompt(): void {
        isDashboardPromptDismissed.value = true;

        try {
            globalThis.localStorage.setItem(
                config.dashboardPromptDismissedStorageKey,
                '1',
            );
        } catch {
            return;
        }
    }

    const cardVariant = computed((): 'missing_config' | 'denied' | 'enable' => {
        if (publicKey.value === null || !browserSupportsPush.value) {
            return 'missing_config';
        }

        if (isPermissionDenied.value) {
            return 'denied';
        }

        return 'enable';
    });

    const remindersEnabled = computed(
        (): boolean => isSubscribedOnServer.value,
    );

    function translate(key: string): string {
        return i18n.global.t(`${config.translationPrefix}.${key}`);
    }

    async function destroySubscriptionsOnServer(): Promise<void> {
        const csrfToken = readCsrfToken();

        const response = await fetch(route(config.destroyRoute), {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken !== null ? { 'X-XSRF-TOKEN': csrfToken } : {}),
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Failed to remove push subscriptions.');
        }
    }

    async function storeSubscriptionOnServer(
        subscription: PushSubscription,
    ): Promise<void> {
        const json = subscription.toJSON() as PushSubscriptionJson;
        const csrfToken = readCsrfToken();

        const response = await fetch(route(config.storeRoute), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken !== null ? { 'X-XSRF-TOKEN': csrfToken } : {}),
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                endpoint: json.endpoint,
                keys: json.keys,
                contentEncoding: 'aesgcm',
            }),
        });

        if (!response.ok) {
            throw new Error('Failed to store push subscription.');
        }
    }

    async function enableReminders(): Promise<void> {
        if (!canEnableReminders.value || publicKey.value === null) {
            return;
        }

        isRegistering.value = true;
        registrationError.value = null;

        try {
            let permissionResult = permission.value;

            if (permissionResult === 'default') {
                permissionResult =
                    await globalThis.Notification.requestPermission();
            }

            if (permissionResult !== 'granted') {
                registrationError.value = translate('registrationDenied');

                return;
            }

            const registration = await registerMedicationPushServiceWorker();

            if (registration === null) {
                registrationError.value = translate(
                    'registrationServiceWorkerFailed',
                );

                return;
            }

            await unsubscribeInBrowser();

            const subscription = await subscribeWithApplicationServerKey(
                registration,
                publicKey.value,
            );

            await storeSubscriptionOnServer(subscription);

            globalThis.location.reload();
        } catch (error) {
            registrationError.value =
                error instanceof Error
                    ? error.message
                    : translate('registrationUnknownError');
        } finally {
            isRegistering.value = false;
        }
    }

    async function disableReminders(): Promise<void> {
        isUnregistering.value = true;
        registrationError.value = null;

        try {
            await unsubscribeInBrowser();
            await destroySubscriptionsOnServer();

            globalThis.location.reload();
        } catch (error) {
            registrationError.value =
                error instanceof Error
                    ? error.message
                    : translate('registrationUnknownError');
        } finally {
            isUnregistering.value = false;
        }
    }

    return {
        translationPrefix: config.translationPrefix,
        browserSupportsPush,
        canEnableReminders,
        remindersEnabled,
        shouldShowCard,
        shouldShowDashboardPrompt,
        dismissDashboardPrompt,
        cardVariant,
        isRegistering,
        isUnregistering,
        registrationError,
        enableReminders,
        disableReminders,
    };
}
