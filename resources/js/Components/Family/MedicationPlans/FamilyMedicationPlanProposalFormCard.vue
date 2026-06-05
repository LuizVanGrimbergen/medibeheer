<script setup lang="ts">
import { computed, ref } from 'vue';
import FamilyMedicationPlanProposalSummaryFooter from '@/Components/Family/MedicationPlans/FamilyMedicationPlanProposalSummaryFooter.vue';
import MedicationFormDialog from '@/Components/Patient/Medications/form/MedicationFormDialog.vue';
import type {
    MedicationCreateFormWithErrors,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { usePatientShellDialogLayout } from '@/composables/patient/usePatientShellDialogLayout';

const props = defineProps<{
    title: string;
    formId: string;
    idPrefix: string;
    form: MedicationCreateFormWithErrors;
    startAtSummary?: boolean;
    summaryHeading?: string;
    publishUrl?: string;
    canPublish?: boolean;
    canAddAnother?: boolean;
}>();

const emit = defineEmits<{
    submit: [];
    cancel: [];
    addAnother: [];
    currentStepChange: [step: MedicationFormWizardStep];
}>();

const open = ref(true);
const { dialogContentClass } = usePatientShellDialogLayout('md');

const showSummaryActions = computed(
    () =>
        props.startAtSummary === true &&
        props.publishUrl !== undefined &&
        (props.canPublish === true || props.canAddAnother === true),
);
</script>

<template>
    <MedicationFormDialog
        v-model:open="open"
        :title="title"
        :summary-heading="summaryHeading"
        :form-id="formId"
        :id-prefix="idPrefix"
        :form="form"
        :dialog-content-class="dialogContentClass"
        :start-at-summary="startAtSummary"
        show-stock-fields
        @submit="$emit('submit')"
        @cancel="$emit('cancel')"
        @current-step-change="$emit('currentStepChange', $event)"
    >
        <template v-if="showSummaryActions" #summaryFooter>
            <FamilyMedicationPlanProposalSummaryFooter
                :publish-url="publishUrl"
                :processing="form.processing"
                :can-publish="canPublish ?? false"
                :can-add-another="canAddAnother ?? false"
                @cancel="$emit('cancel')"
                @add-another="$emit('addAnother')"
            />
        </template>
    </MedicationFormDialog>
</template>
