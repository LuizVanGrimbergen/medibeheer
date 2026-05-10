<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';

const dismissedStorageKey = 'medibeheer:pwa-ios-install-banner-dismissed';

const { t } = useI18n();
const showBanner = ref(false);

function hasDebugInstallBannerFlag(): boolean {
    if (globalThis.window === undefined) {
        return false;
    }

    return new URLSearchParams(globalThis.window.location.search).get('pwaInstallBanner') === '1';
}

function isStandaloneDisplayMode(): boolean {
    if (globalThis.window === undefined) {
        return false;
    }

    const mediaStandalone = globalThis.window.matchMedia?.('(display-mode: standalone)').matches === true;
    const navigatorStandalone =
        'standalone' in globalThis.navigator &&
        (globalThis.navigator as Navigator & { standalone?: boolean }).standalone === true;

    return mediaStandalone || navigatorStandalone;
}

function isIosSafari(): boolean {
    if (globalThis.window === undefined) {
        return false;
    }

    const navigator = globalThis.window.navigator;
    const userAgent = globalThis.navigator.userAgent;
    const isIosDevice = /iPhone|iPad|iPod/i.test(userAgent);
    const isIpadOsDesktopMode = navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1;
    const isAppleMobileDevice = isIosDevice || isIpadOsDesktopMode;
    const usesSafariEngine = /WebKit/i.test(userAgent);
    const isOtherBrowserShell = /CriOS|FxiOS|EdgiOS|OPiOS|DuckDuckGo|YaBrowser/i.test(userAgent);
    const hasSafariToken = /Safari/i.test(userAgent);

    return isAppleMobileDevice && usesSafariEngine && hasSafariToken && !isOtherBrowserShell;
}

function wasDismissedBefore(): boolean {
    try {
        return globalThis.window.localStorage.getItem(dismissedStorageKey) === '1';
    } catch {
        return false;
    }
}

onMounted(() => {
    if (hasDebugInstallBannerFlag()) {
        showBanner.value = true;

        return;
    }

    if (!isIosSafari()) {
        return;
    }

    if (isStandaloneDisplayMode()) {
        return;
    }

    if (wasDismissedBefore()) {
        return;
    }

    showBanner.value = true;
});

function dismiss(): void {
    showBanner.value = false;

    try {
        globalThis.window.localStorage.setItem(dismissedStorageKey, '1');
    } catch {
        return;
    }
}
</script>

<template>
    <Alert
        v-if="showBanner"
        class="rounded-2xl border-2 border-primary/35 bg-surface text-text shadow-md shadow-black/[0.04] sm:rounded-3xl"
    >
        <AlertTitle class="text-lg font-semibold leading-snug text-text-heading sm:text-xl">
            {{ t('app.pwa.iosInstallTitle') }}
        </AlertTitle>

        <AlertDescription class="space-y-3 sm:space-y-4">
            <p class="text-base leading-relaxed text-text-muted sm:text-lg">
                {{ t('app.pwa.iosInstallSubtitle') }}
            </p>

            <ol
                class="list-decimal space-y-2 pl-6 text-base leading-relaxed text-text sm:text-lg"
                :aria-label="t('app.pwa.iosInstallAriaLabel')"
            >
                <li>{{ t('app.pwa.iosInstallStepOpenShare') }}</li>
                <li>{{ t('app.pwa.iosInstallStepAddHome') }}</li>
                <li>{{ t('app.pwa.iosInstallStepOpenApp') }}</li>
            </ol>

            <p class="text-base leading-relaxed text-text-muted sm:text-lg">
                {{ t('app.pwa.iosInstallHelp') }}
            </p>

            <div class="flex justify-end pt-1">
                <Button
                    type="button"
                    variant="ghost"
                    size="lg"
                    class="min-h-12 px-5 text-base font-semibold sm:min-h-14 sm:text-lg"
                    @click="dismiss"
                >
                    {{ t('app.pwa.dismiss') }}
                </Button>
            </div>
        </AlertDescription>
    </Alert>
</template>
