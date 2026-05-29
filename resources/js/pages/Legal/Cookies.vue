<script setup lang="ts">
import LegalDocumentLayout from '@/Components/Legal/LegalDocumentLayout.vue';
import { useI18n } from 'vue-i18n';

defineProps<{
    policyVersion: string;
}>();

const { t } = useI18n();

const sectionKeys = ['necessary', 'storage', 'analytics'] as const;
</script>

<template>
    <LegalDocumentLayout
        title-key="privacy.cookies.title"
        meta-title-key="privacy.cookies.metaTitle"
        :policy-version="policyVersion"
    >
        <section
            v-for="key in sectionKeys"
            :key="key"
        >
            <h2 class="text-xl font-semibold text-primary">
                {{ t(`privacy.cookies.sections.${key}.title`) }}
            </h2>
            <p class="mt-2 text-text-muted">
                {{ t(`privacy.cookies.sections.${key}.body`) }}
            </p>
        </section>
    </LegalDocumentLayout>
</template>
