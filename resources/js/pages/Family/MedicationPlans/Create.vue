<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import FamilyMedicationPlanProposalFormCard from '@/Components/Family/MedicationPlans/FamilyMedicationPlanProposalFormCard.vue';
import { useFamilyMedicationPlanProposalFormPage } from '@/composables/useFamilyMedicationPlanProposalFormPage';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyDashboardProps, PageProps } from '@/lib/types';

type FamilyMedicationPlansPageProps = PageProps & {
    family?: FamilyDashboardProps;
};

const { t } = useI18n();
const page = usePage<FamilyMedicationPlansPageProps>();

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
        <FamilyPageShell
            :title="t('family.medicationPlans.createTitle')"
            :family="page.props.family"
            :show-active-patient="
                page.props.family?.has_linked_patient ?? false
            "
        >
            <FamilyMedicationPlanProposalFormCard
                :title="t('family.medicationPlans.createTitle')"
                form-id="family-medication-plan-create"
                id-prefix="family-medication-plan-create"
                :form="form"
                @submit="submit()"
                @cancel="cancel"
            />
        </FamilyPageShell>
    </FamilyLayout>
</template>
