<script setup lang="ts">
import { Loader2 } from 'lucide-vue-next';
import { computed, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type { LoadingScreenMessageKey } from '@/lib/navigation/inertiaLoadingScreenPolicy';
import { loadingScreenMinVisibleMs } from '@/lib/navigation/inertiaLoadingScreenPolicy';
import { animateLoadingScreenExit } from '@/lib/motion/gsapMotion';

const open = defineModel<boolean>('open', { required: true });

const props = withDefaults(
    defineProps<{
        messageKey?: LoadingScreenMessageKey;
    }>(),
    {
        messageKey: 'default',
    },
);

const overlayRef = ref<HTMLElement | null>(null);
const isRendered = ref(false);

const { t } = useI18n();

const title = computed(() =>
    t(`app.loading.${props.messageKey}.title`),
);

const description = computed(() =>
    t(`app.loading.${props.messageKey}.description`),
);

let shownAtMs = 0;
let hideDelayTimeoutId: ReturnType<typeof globalThis.setTimeout> | null = null;
let exitTween: ReturnType<typeof animateLoadingScreenExit> | null = null;

const clearHideDelayTimeout = (): void => {
    if (hideDelayTimeoutId === null) {
        return;
    }

    globalThis.clearTimeout(hideDelayTimeoutId);
    hideDelayTimeoutId = null;
};

const hideOverlay = (): void => {
    clearHideDelayTimeout();
    exitTween?.kill();
    exitTween = null;
    isRendered.value = false;
};

const scheduleHideOverlay = (): void => {
    clearHideDelayTimeout();

    const elapsedMs = Date.now() - shownAtMs;
    const remainingMs = Math.max(0, loadingScreenMinVisibleMs() - elapsedMs);

    hideDelayTimeoutId = globalThis.setTimeout(() => {
        hideDelayTimeoutId = null;

        const overlay = overlayRef.value;

        if (overlay === null) {
            hideOverlay();

            return;
        }

        exitTween?.kill();
        exitTween = animateLoadingScreenExit(overlay, hideOverlay);
    }, remainingMs);
};

watch(
    open,
    (isOpen) => {
        if (isOpen) {
            clearHideDelayTimeout();
            exitTween?.kill();
            exitTween = null;
            isRendered.value = true;
            shownAtMs = Date.now();

            return;
        }

        if (!isRendered.value) {
            return;
        }

        scheduleHideOverlay();
    },
    { immediate: true },
);

onUnmounted(() => {
    clearHideDelayTimeout();
    exitTween?.kill();
    exitTween = null;
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="isRendered"
            ref="overlayRef"
            class="bg-bg/80 fixed inset-0 z-[120] flex touch-none items-center justify-center overscroll-none max-md:backdrop-blur-none md:backdrop-blur-sm"
            role="status"
            aria-live="polite"
            aria-busy="true"
            :aria-label="t('app.loading.ariaLabel')"
        >
            <div
                class="border-border bg-surface mx-4 flex w-full max-w-sm flex-col items-center gap-4 rounded-2xl border px-8 py-7 text-center shadow-lg"
            >
                <Loader2
                    class="text-primary size-10 shrink-0 animate-spin"
                    aria-hidden="true"
                />
                <div class="space-y-1">
                    <p class="text-text-heading text-lg font-bold">
                        {{ title }}
                    </p>
                    <p class="text-text-muted text-base leading-relaxed">
                        {{ description }}
                    </p>
                </div>
            </div>
        </div>
    </Teleport>
</template>
