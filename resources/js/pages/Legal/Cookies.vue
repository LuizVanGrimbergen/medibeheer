<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import LegalDocumentLayout from '@/Components/Legal/LegalDocumentLayout.vue';

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
