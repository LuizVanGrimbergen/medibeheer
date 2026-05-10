<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PwaIosInstallBanner from '@/Components/PwaIosInstallBanner.vue';
import { FlashErrorBanner } from '@/Components/ui/flash-error-banner';
import type { PageProps } from '@/lib/types';
import AuthPageHeader from './AuthPageHeader.vue';

const props = withDefaults(
    defineProps<{
        titleKey: string;
        subtitleKey: string;
        showBranding?: boolean;
        appendAppName?: boolean;
    }>(),
    {
        showBranding: false,
        appendAppName: false,
    },
);

const page = usePage<PageProps>();
const flashError = computed(() => page.props.flash?.error ?? null);
const flashRateLimitSeconds = computed(() => page.props.flash?.rateLimitSeconds ?? null);
</script>

<template>
    <div class="min-h-dvh bg-bg pt-[env(safe-area-inset-top)] pb-[env(safe-area-inset-bottom)] md:flex md:items-center md:justify-center">
        <div class="mx-auto w-full max-w-xl px-4 py-8 sm:px-6 sm:py-10 md:py-12">
            <slot name="top" />
            <PwaIosInstallBanner class="mb-4" />

            <AuthPageHeader
                :title-key="props.titleKey"
                :subtitle-key="props.subtitleKey"
                :show-branding="props.showBranding"
                :append-app-name="props.appendAppName"
            />

            <FlashErrorBanner
                v-if="flashError"
                class="mb-4"
                :message="flashError"
                :rate-limit-seconds="flashRateLimitSeconds"
            />

            <slot />
        </div>
    </div>
</template>
