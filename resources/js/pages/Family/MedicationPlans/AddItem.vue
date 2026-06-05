<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FamilyMedicationPlanProposalFormCard from '@/Components/Family/MedicationPlans/FamilyMedicationPlanProposalFormCard.vue';
import { Button } from '@/Components/ui/button';
import { useFamilyMedicationPlanProposalFormPage } from '@/composables/family/useFamilyMedicationPlanProposalFormPage';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';

const props = defineProps<{
    proposal_id: number;
}>();

const { t } = useI18n();

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
        <div
            class="pointer-events-none fixed inset-x-0 top-0 z-[109] px-4 pt-[max(1rem,env(safe-area-inset-top))] md:px-6"
        >
            <Button
                as-child
                variant="ghost"
                class="pointer-events-auto w-fit px-0"
            >
                <Link
                    :href="
                        route('family.medication-plans.edit', props.proposal_id)
                    "
                >
                    {{ t('family.medicationPlans.backToEdit') }}
                </Link>
            </Button>
        </div>

        <FamilyMedicationPlanProposalFormCard
            :title="t('family.medicationPlans.addItemTitle')"
            form-id="family-medication-plan-add-item"
            id-prefix="family-medication-plan-add-item"
            :form="form"
            @submit="submit()"
            @cancel="cancel"
        />
    </FamilyLayout>
</template>
