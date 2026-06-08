<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
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

const controllerDetails = computed((): string => {
    const parts: string[] = [];

    if (props.controller.address !== null) {
        parts.push(
            t('privacy.controllerAddressLabel', {
                address: props.controller.address,
            }),
        );
    }

    if (props.controller.kbo !== null) {
        parts.push(
            t('privacy.controllerKboLabel', { kbo: props.controller.kbo }),
        );
    }

    if (parts.length === 0) {
        return '';
    }

    return ` ${parts.join(' ')}`;
});

const sectionParams = (key: (typeof sectionKeys)[number]) => {
    const shared = {
        contactEmail: props.contactEmail,
        controllerName: props.controller.name,
        controllerDetails: controllerDetails.value,
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
        :document-version="policyVersion"
        :document-locale="documentLocale"
        locale-route-name="legal.privacy"
    >
        <section v-for="key in sectionKeys" :key="key">
            <h2 class="text-primary text-xl font-semibold">
                {{ t(`privacy.sections.${key}.title`) }}
            </h2>
            <p class="text-text-muted mt-2">
                {{ t(`privacy.sections.${key}.body`, sectionParams(key)) }}
            </p>
            <p v-if="key === 'controller'" class="text-text-muted mt-2">
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
