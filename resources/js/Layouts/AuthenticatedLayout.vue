<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppNavbar from '@/Components/AppNavbar.vue';
import { FlashErrorBanner } from '@/Components/ui/flash-error-banner';
import type { PageProps } from '@/lib/types';

const page = usePage<PageProps>();
const authenticatedUserName = computed(() => page.props.auth.user?.name ?? '');
const flashError = computed(() => page.props.flash?.error ?? null);
const flashSuccess = computed(() => page.props.flash?.success ?? null);
const flashRateLimitSeconds = computed(() => page.props.flash?.rateLimitSeconds ?? null);
</script>

<template>
    <div class="min-h-screen bg-bg">
        <AppNavbar :user-name="authenticatedUserName" />

        <header
            v-if="$slots.header"
            class="border-b border-border bg-surface"
        >
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="min-w-0 overflow-x-hidden">
            <div
                v-if="flashError"
                class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8"
            >
                <FlashErrorBanner
                    :message="flashError"
                    :rate-limit-seconds="flashRateLimitSeconds"
                />
            </div>
            <div
                v-if="flashSuccess"
                class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8"
            >
                <output
                    class="rounded-xl border border-success/40 bg-success/10 px-4 py-3 text-sm text-text"
                >
                    {{ flashSuccess }}
                </output>
            </div>
            <slot />
        </main>
    </div>
</template>
