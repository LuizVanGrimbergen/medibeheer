<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import PatientFormWizardFooter from '@/Components/Patient/form/PatientFormWizardFooter.vue';
import PatientFormWizardFooterButton from '@/Components/Patient/form/PatientFormWizardFooterButton.vue';

const props = defineProps<{
    publishUrl?: string;
    processing: boolean;
    canPublish: boolean;
    canAddAnother: boolean;
}>();

const emit = defineEmits<{
    cancel: [];
    addAnother: [];
}>();

const { t } = useI18n();
</script>

<template>
    <PatientFormWizardFooter>
        <PatientFormWizardFooterButton
            v-if="canPublish && props.publishUrl !== undefined"
            variant="primary"
            :href="props.publishUrl"
            :disabled="processing"
        >
            {{ t('family.medicationPlans.form.share') }}
        </PatientFormWizardFooterButton>

        <PatientFormWizardFooterButton
            v-if="canAddAnother"
            variant="outline"
            :disabled="processing"
            @click="emit('addAnother')"
        >
            {{ t('family.medicationPlans.form.addAnotherMedication') }}
        </PatientFormWizardFooterButton>

        <PatientFormWizardFooterButton
            variant="danger"
            :disabled="processing"
            @click="emit('cancel')"
        >
            {{ t('family.medicationPlans.form.cancel') }}
        </PatientFormWizardFooterButton>
    </PatientFormWizardFooter>
</template>
