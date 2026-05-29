<script setup lang="ts">
import LegalDocumentLayout from '@/Components/Legal/LegalDocumentLayout.vue';
import { useI18n } from 'vue-i18n';

defineProps<{
    policyVersion: string;
}>();

const { t } = useI18n();

const sectionKeys = [
    'controller',
    'data',
    'purpose',
    'legalBasis',
    'retention',
    'rights',
    'processors',
] as const;
</script>

<template>
    <LegalDocumentLayout
        title-key="privacy.title"
        meta-title-key="privacy.metaTitle"
        :policy-version="policyVersion"
    >
        <section
            v-for="key in sectionKeys"
            :key="key"
        >
            <h2 class="text-xl font-semibold text-primary">
                {{ t(`privacy.sections.${key}.title`) }}
            </h2>
            <p class="mt-2 text-text-muted">
                {{ t(`privacy.sections.${key}.body`) }}
            </p>
        </section>
    </LegalDocumentLayout>
</template>
