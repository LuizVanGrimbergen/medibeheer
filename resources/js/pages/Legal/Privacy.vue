<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import LegalDocumentLayout from '@/Components/Legal/LegalDocumentLayout.vue';

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
        <section v-for="key in sectionKeys" :key="key">
            <h2 class="text-primary text-xl font-semibold">
                {{ t(`privacy.sections.${key}.title`) }}
            </h2>
            <p class="text-text-muted mt-2">
                {{ t(`privacy.sections.${key}.body`) }}
            </p>
        </section>
    </LegalDocumentLayout>
</template>
