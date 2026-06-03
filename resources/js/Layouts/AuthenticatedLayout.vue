<script setup lang="ts">
import AppNavbar from '@/Components/AppNavbar.vue';
import PwaIosInstallBanner from '@/Components/PwaIosInstallBanner.vue';
import { FlashErrorBanner } from '@/Components/ui/flash-error-banner';
import { FlashSuccessBanner } from '@/Components/ui/flash-success-banner';
import { useInertiaNavigationLoading } from '@/composables/useInertiaNavigationLoading';
import type { PageProps } from '@/lib/types';
import { usePage } from '@inertiajs/vue3';
import { computed, defineAsyncComponent } from 'vue';

const AppLoadingScreen = defineAsyncComponent(
    () => import('@/Components/ui/loading-screen/AppLoadingScreen.vue'),
);

const { isLoading: isNavigationLoading, loadingMessageKey } =
    useInertiaNavigationLoading();

const page = usePage<PageProps>();
const authenticatedUserName = computed(() => page.props.auth.user?.name ?? '');
const flashError = computed(() => page.props.flash?.error ?? null);
const flashSuccess = computed(() => page.props.flash?.success ?? null);
const flashRateLimitSeconds = computed(
    () => page.props.flash?.rateLimitSeconds ?? null,
);
</script>

<template>
    <div class="bg-bg flex h-svh max-h-svh min-h-0 flex-col overflow-hidden">
        <AppLoadingScreen
            v-model:open="isNavigationLoading"
            :message-key="loadingMessageKey"
        />

        <AppNavbar :user-name="authenticatedUserName" />
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <PwaIosInstallBanner />
        </div>

        <header v-if="$slots.header" class="border-border bg-surface border-b">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main
            class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden overflow-x-hidden"
        >
            <div
                v-if="flashError"
                class="mx-auto mt-6 max-w-7xl shrink-0 px-4 sm:px-6 lg:px-8"
            >
                <FlashErrorBanner
                    :message="flashError"
                    :rate-limit-seconds="flashRateLimitSeconds"
                />
            </div>
            <div
                v-if="flashSuccess"
                class="mx-auto mt-6 max-w-7xl shrink-0 px-4 sm:px-6 lg:px-8"
            >
                <FlashSuccessBanner :message="flashSuccess" />
            </div>
            <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
                <slot />
            </div>
        </main>
    </div>
</template>
