<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import LegalDocumentLayout from '@/Components/Legal/LegalDocumentLayout.vue';

defineProps<{
    policyVersion: string;
    termsVersion: string;
    contactEmail: string;
    controller: {
        name: string;
        address: string | null;
        kbo: string | null;
    };
    documentLocale: string;
    retention: {
        security_activity_log_days: number;
        data_activity_log_days: number;
        session_days: number;
    };
}>();

const { t } = useI18n();

const sectionKeys = [
    'necessary',
    'storage',
    'push',
    'publicSite',
    'analytics',
] as const;
</script>

<template>
    <LegalDocumentLayout
        title-key="privacy.cookies.title"
        meta-title-key="privacy.cookies.metaTitle"
        meta-description-key="privacy.cookies.metaDescription"
        :document-version="policyVersion"
        :document-locale="documentLocale"
        locale-route-name="legal.cookies"
    >
        <section v-for="key in sectionKeys" :key="key">
            <h2 class="text-primary text-xl font-semibold">
                {{ t(`privacy.cookies.sections.${key}.title`) }}
            </h2>
            <p class="text-text-muted mt-2">
                {{ t(`privacy.cookies.sections.${key}.body`) }}
            </p>
        </section>
    </LegalDocumentLayout>
</template>
