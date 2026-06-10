<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PatientFamilySectionCard from '@/Components/Patient/Family/PatientFamilySectionCard.vue';
import PatientFormWizardFooter from '@/Components/Patient/form/PatientFormWizardFooter.vue';
import PatientFormWizardFooterButton from '@/Components/Patient/form/PatientFormWizardFooterButton.vue';
import { formatCareTeamExpiry } from '@/lib/patient/careTeam/formatCareTeamExpiry';
import {
    mobileShellPageSectionInnerRowClass,
} from '@/lib/shell/mobileShellLayout';
import {
    mobileShellSectionBodyTextClass,
    mobileShellSectionHeadingClass,
    mobileShellSectionSubHeadingClass,
} from '@/lib/shell/mobileShellTypography';
import type { PendingMedicationPlanProposal } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        pendingMedicationPlans?: PendingMedicationPlanProposal[];
    }>(),
    {
        pendingMedicationPlans: () => [],
    },
);

const { t } = useI18n();

function acceptMedicationPlan(proposal: PendingMedicationPlanProposal): void {
    router.post(proposal.accept_url, {}, { preserveScroll: true });
}

function declineMedicationPlan(proposal: PendingMedicationPlanProposal): void {
    router.post(proposal.decline_url, {}, { preserveScroll: true });
}

function reviewMedicationPlan(proposal: PendingMedicationPlanProposal): void {
    router.visit(proposal.review_url);
}
</script>

<template>
    <PatientFamilySectionCard
        v-if="props.pendingMedicationPlans.length > 0"
        id="family-pending-plans"
        scroll-margin
        aria-labelledby="family-action-required-heading"
    >
        <h2
            id="family-action-required-heading"
            :class="mobileShellSectionHeadingClass"
        >
            {{ t('patient.family.actionRequiredHeading') }}
        </h2>
        <p class="mt-3" :class="mobileShellSectionBodyTextClass">
            {{ t('patient.family.actionRequiredIntro') }}
        </p>

        <ul class="mt-6 flex flex-col gap-4">
            <li
                v-for="plan in props.pendingMedicationPlans"
                :key="plan.id"
                :class="mobileShellPageSectionInnerRowClass"
            >
                <div class="min-w-0 space-y-1">
                    <p :class="mobileShellSectionSubHeadingClass">
                        {{
                            plan.medication_name ??
                            t('family.medicationPlans.unnamed')
                        }}
                    </p>
                    <p
                        v-if="plan.family_member_name !== ''"
                        :class="mobileShellSectionBodyTextClass"
                    >
                        {{
                            t('patient.family.pendingMedicationPlansFrom', {
                                name: plan.family_member_name,
                            })
                        }}
                    </p>
                    <p
                        v-if="plan.expires_at !== null"
                        class="text-text-muted text-sm md:text-base"
                    >
                        {{
                            t('patient.family.expiresAt', {
                                date: formatCareTeamExpiry(plan.expires_at),
                            })
                        }}
                    </p>
                </div>

                <PatientFormWizardFooter class="pt-4">
                    <PatientFormWizardFooterButton
                        variant="primary"
                        @click="acceptMedicationPlan(plan)"
                    >
                        {{
                            t('patient.family.pendingMedicationPlansAccept')
                        }}
                    </PatientFormWizardFooterButton>
                    <PatientFormWizardFooterButton
                        variant="outline"
                        @click="reviewMedicationPlan(plan)"
                    >
                        {{
                            t('patient.family.pendingMedicationPlansReview')
                        }}
                    </PatientFormWizardFooterButton>
                    <PatientFormWizardFooterButton
                        variant="danger"
                        @click="declineMedicationPlan(plan)"
                    >
                        {{
                            t('patient.family.pendingMedicationPlansDecline')
                        }}
                    </PatientFormWizardFooterButton>
                </PatientFormWizardFooter>
            </li>
        </ul>
    </PatientFamilySectionCard>
</template>
