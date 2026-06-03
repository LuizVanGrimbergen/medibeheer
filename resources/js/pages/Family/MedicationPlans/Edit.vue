<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import FamilyMedicationPlanProposalActionsPanel from '@/Components/Family/MedicationPlans/FamilyMedicationPlanProposalActionsPanel.vue';
import FamilyMedicationPlanProposalFormCard from '@/Components/Family/MedicationPlans/FamilyMedicationPlanProposalFormCard.vue';
import type { MedicationFormWizardStep } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { useFamilyMedicationPlanProposalFormPage } from '@/composables/useFamilyMedicationPlanProposalFormPage';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { MedicationPlanProposalFormInitial } from '@/lib/family/medicationPlans/medicationPlanProposalInitialToFormState';
import type { FamilyDashboardProps, PageProps } from '@/lib/types';

type FamilyMedicationPlansPageProps = PageProps & {
    family?: FamilyDashboardProps;
};

const props = defineProps<{
    proposal_id: number;
    item_id: number | null;
    initial: MedicationPlanProposalFormInitial | null;
    show_summary?: boolean;
}>();

const { t } = useI18n();
const page = usePage<FamilyMedicationPlansPageProps>();

const wizardCurrentStep = ref<MedicationFormWizardStep>(
    props.initial !== null || (props.show_summary ?? false) ? 7 : 1,
);

const startAtSummary = computed(
    () => props.initial !== null || (props.show_summary ?? false),
);

const showExternalActions = computed(() => wizardCurrentStep.value === 7);

const publishUrl = route('family.medication-plans.publish', props.proposal_id);

const { form, submit } = useFamilyMedicationPlanProposalFormPage({
    mode: 'edit',
    proposalId: props.proposal_id,
    itemId: props.item_id,
    initial: props.initial,
});

function cancel(): void {
    router.visit(route('family.link'));
}

function saveAndAddAnother(): void {
    submit({ thenAddAnother: true });
}
</script>

<template>
    <Head>
        <title>{{ t('family.medicationPlans.editTitle') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyPageShell
            :title="t('family.medicationPlans.editTitle')"
            :family="page.props.family"
            :show-active-patient="page.props.family?.has_linked_patient ?? false"
        >
            <FamilyMedicationPlanProposalFormCard
                v-if="!showExternalActions"
                :title="t('family.medicationPlans.editTitle')"
                form-id="family-medication-plan-edit"
                id-prefix="family-medication-plan-edit"
                :form="form"
                :start-at-summary="startAtSummary"
                @submit="submit()"
                @cancel="cancel"
                @current-step-change="wizardCurrentStep = $event"
            />

            <FamilyMedicationPlanProposalActionsPanel
                v-if="showExternalActions"
                :publish-url="publishUrl"
                :processing="form.processing"
                :can-publish="true"
                :can-add-another="true"
                @cancel="cancel"
                @add-another="saveAndAddAnother"
            />
        </FamilyPageShell>
    </FamilyLayout>
</template>
