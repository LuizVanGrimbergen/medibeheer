<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyMedicationPlanProposalFormCard from '@/Components/Family/MedicationPlans/FamilyMedicationPlanProposalFormCard.vue';
import { useFamilyMedicationPlanProposalFormPage } from '@/composables/family/useFamilyMedicationPlanProposalFormPage';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { MedicationPlanProposalFormInitial } from '@/lib/family/medicationPlans/medicationPlanProposalInitialToFormState';

const props = defineProps<{
    proposal_id: number;
    item_id: number | null;
    initial: MedicationPlanProposalFormInitial | null;
    show_summary?: boolean;
}>();

const { t } = useI18n();

const startAtSummary = computed(
    () => props.initial !== null || (props.show_summary ?? false),
);

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
        <FamilyMedicationPlanProposalFormCard
            :title="t('family.medicationPlans.editTitle')"
            :summary-heading="
                startAtSummary
                    ? t('family.medicationPlans.actionsTitle')
                    : undefined
            "
            form-id="family-medication-plan-edit"
            id-prefix="family-medication-plan-edit"
            :form="form"
            :start-at-summary="startAtSummary"
            :publish-url="startAtSummary ? publishUrl : undefined"
            :can-publish="startAtSummary"
            :can-add-another="startAtSummary"
            @submit="submit()"
            @cancel="cancel"
            @add-another="saveAndAddAnother"
        />
    </FamilyLayout>
</template>
