<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import LegalDocumentLayout from '@/Components/Legal/LegalDocumentLayout.vue';

const props = defineProps<{
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
    'nature',
    'notMedicalDevice',
    'userResponsibility',
    'noEmergency',
    'medicalDisclaimer',
    'ai',
    'liability',
    'riziv',
    'applicableLaw',
    'changes',
] as const;

const sectionParams = () => ({
    contactEmail: props.contactEmail,
    controllerName: props.controller.name,
});
</script>

<template>
    <LegalDocumentLayout
        title-key="legal.title"
        meta-title-key="legal.metaTitle"
        meta-description-key="legal.metaDescription"
        :document-version="termsVersion"
        version-label-key="legal.versionLabel"
        :document-locale="documentLocale"
        locale-route-name="legal.terms"
    >
        <section v-for="key in sectionKeys" :key="key">
            <h2 class="text-primary text-xl font-semibold">
                {{ t(`legal.sections.${key}.title`) }}
            </h2>
            <p class="text-text-muted mt-2">
                {{ t(`legal.sections.${key}.body`, sectionParams()) }}
            </p>
        </section>
    </LegalDocumentLayout>
</template>
