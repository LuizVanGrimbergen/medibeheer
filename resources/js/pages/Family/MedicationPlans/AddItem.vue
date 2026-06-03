<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import FamilyMedicationPlanProposalFormCard from '@/Components/Family/MedicationPlans/FamilyMedicationPlanProposalFormCard.vue';
import { Button } from '@/Components/ui/button';
import { useFamilyMedicationPlanProposalFormPage } from '@/composables/useFamilyMedicationPlanProposalFormPage';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyDashboardProps, PageProps } from '@/lib/types';

type FamilyMedicationPlansPageProps = PageProps & {
    family?: FamilyDashboardProps;
};

const props = defineProps<{
    proposal_id: number;
}>();

const { t } = useI18n();
const page = usePage<FamilyMedicationPlansPageProps>();

const { form, submit } = useFamilyMedicationPlanProposalFormPage({
    mode: 'addItem',
    proposalId: props.proposal_id,
});

function cancel(): void {
    router.visit(route('family.link'));
}
</script>

<template>
    <Head>
        <title>{{ t('family.medicationPlans.addItemTitle') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyPageShell
            :title="t('family.medicationPlans.addItemTitle')"
            :family="page.props.family"
            :show-active-patient="page.props.family?.has_linked_patient ?? false"
        >
            <div class="flex flex-col gap-4">
                <Button
                    as-child
                    variant="ghost"
                    class="w-fit px-0"
                >
                    <Link :href="route('family.medication-plans.edit', props.proposal_id)">
                        {{ t('family.medicationPlans.backToEdit') }}
                    </Link>
                </Button>

                <FamilyMedicationPlanProposalFormCard
                    :title="t('family.medicationPlans.addItemTitle')"
                    form-id="family-medication-plan-add-item"
                    id-prefix="family-medication-plan-add-item"
                    :form="form"
                    @submit="submit()"
                    @cancel="cancel"
                />
            </div>
        </FamilyPageShell>
    </FamilyLayout>
</template>
