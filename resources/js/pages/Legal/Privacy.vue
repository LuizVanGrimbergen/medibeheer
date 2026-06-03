<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LegalDocumentLayout from '@/Components/Legal/LegalDocumentLayout.vue';

const props = defineProps<{
    policyVersion: string;
    contactEmail: string;
    retention: {
        security_activity_log_days: number;
        data_activity_log_days: number;
        session_days: number;
    };
}>();

const { t } = useI18n();

const sectionKeys = [
    'controller',
    'data',
    'purpose',
    'legalBasis',
    'sharing',
    'retention',
    'security',
    'rights',
    'processors',
    'changes',
] as const;

const sectionParams = (key: (typeof sectionKeys)[number]) => {
    const shared = {
        contactEmail: props.contactEmail,
    };

    if (key === 'retention') {
        return {
            ...shared,
            securityLogDays: props.retention.security_activity_log_days,
            dataLogDays: props.retention.data_activity_log_days,
            sessionDays: props.retention.session_days,
        };
    }

    return shared;
};
</script>

<template>
    <LegalDocumentLayout
        title-key="privacy.title"
        meta-title-key="privacy.metaTitle"
        meta-description-key="privacy.metaDescription"
        :policy-version="policyVersion"
    >
        <section v-for="key in sectionKeys" :key="key">
            <h2 class="text-primary text-xl font-semibold">
                {{ t(`privacy.sections.${key}.title`) }}
            </h2>
            <p class="text-text-muted mt-2">
                {{ t(`privacy.sections.${key}.body`, sectionParams(key)) }}
            </p>
            <p
                v-if="key === 'controller'"
                class="text-text-muted mt-2"
            >
                {{ t('privacy.cookiesReferencePrefix') }}
                <Link
                    :href="route('legal.cookies')"
                    class="text-primary font-semibold hover:opacity-80"
                >
                    {{ t('privacy.cookiesLinkLabel') }}
                </Link>
                {{ t('privacy.cookiesReferenceSuffix') }}
            </p>
        </section>
    </LegalDocumentLayout>
</template>
