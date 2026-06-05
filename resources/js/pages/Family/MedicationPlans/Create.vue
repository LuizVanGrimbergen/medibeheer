<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FamilyMedicationPlanProposalFormCard from '@/Components/Family/MedicationPlans/FamilyMedicationPlanProposalFormCard.vue';
import { useFamilyMedicationPlanProposalFormPage } from '@/composables/family/useFamilyMedicationPlanProposalFormPage';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';

const { t } = useI18n();

const { form, submit } = useFamilyMedicationPlanProposalFormPage({
    mode: 'create',
});

function cancel(): void {
    router.visit(route('family.link'));
}
</script>

<template>
    <Head>
        <title>{{ t('family.medicationPlans.createTitle') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyMedicationPlanProposalFormCard
            :title="t('family.medicationPlans.createTitle')"
            form-id="family-medication-plan-create"
            id-prefix="family-medication-plan-create"
            :form="form"
            @submit="submit()"
            @cancel="cancel"
        />
    </FamilyLayout>
</template>
