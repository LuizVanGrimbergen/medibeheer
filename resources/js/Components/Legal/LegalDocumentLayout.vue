<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    titleKey: string;
    metaTitleKey: string;
    policyVersion: string;
}>();

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ t(props.metaTitleKey) }}</title>
    </Head>

    <div class="min-h-dvh bg-bg pt-[env(safe-area-inset-top)] pb-[env(safe-area-inset-bottom)]">
        <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 sm:py-10">
            <Link
                :href="route('login')"
                class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:opacity-80"
            >
                <ArrowLeft :size="18" />
                <span>{{ t('privacy.back') }}</span>
            </Link>

            <h1 class="text-3xl font-bold text-text">
                {{ t(props.titleKey) }}
            </h1>
            <p class="mt-2 text-sm text-text-muted">
                {{ t('privacy.versionLabel', { version: props.policyVersion }) }}
            </p>

            <div class="mt-8 max-w-none space-y-8 text-base leading-relaxed text-text">
                <slot />
            </div>
        </div>
    </div>
</template>
