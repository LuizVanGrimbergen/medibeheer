<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PatientFamilySectionCard from '@/Components/Patient/Family/PatientFamilySectionCard.vue';
import { buttonVariants } from '@/Components/ui/button';
import { formatCareTeamExpiry } from '@/lib/patient/careTeam/formatCareTeamExpiry';
import {
    mobileShellSectionBodyTextClass,
    mobileShellSectionHeadingClass,
} from '@/lib/shell/mobileShellTypography';
import {
    mobileShellActionOutlineButtonClass,
    mobileShellActionPrimaryButtonClass,
    mobileShellActionSecondaryOutlineButtonClass,
} from '@/lib/shell/mobileShellDialogLayout';
import {
    mobileShellPageSectionInnerRowClass,
} from '@/lib/shell/mobileShellLayout';
import type { PendingMedicationPlanProposal } from '@/lib/types';
import { cn } from '@/lib/utils';

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
        variant="accent"
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
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div class="min-w-0 space-y-1">
                        <p
                            class="text-text-heading text-lg leading-snug font-bold md:text-xl"
                        >
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

                    <div
                        class="flex w-full min-w-0 flex-col gap-2 sm:flex-row-reverse sm:gap-3"
                    >
                        <button
                            type="button"
                            :class="
                                cn(
                                    buttonVariants({
                                        variant: 'default',
                                        size: 'lg',
                                    }),
                                    mobileShellActionPrimaryButtonClass,
                                )
                            "
                            @click="acceptMedicationPlan(plan)"
                        >
                            {{
                                t('patient.family.pendingMedicationPlansAccept')
                            }}
                        </button>
                        <button
                            type="button"
                            :class="
                                cn(
                                    buttonVariants({
                                        variant: 'outline',
                                        size: 'lg',
                                    }),
                                    mobileShellActionOutlineButtonClass,
                                )
                            "
                            @click="reviewMedicationPlan(plan)"
                        >
                            {{
                                t('patient.family.pendingMedicationPlansReview')
                            }}
                        </button>
                        <button
                            type="button"
                            :class="
                                cn(
                                    buttonVariants({
                                        variant: 'outline',
                                        size: 'lg',
                                    }),
                                    mobileShellActionSecondaryOutlineButtonClass,
                                )
                            "
                            @click="declineMedicationPlan(plan)"
                        >
                            {{
                                t('patient.family.pendingMedicationPlansDecline')
                            }}
                        </button>
                    </div>
                </div>
            </li>
        </ul>
    </PatientFamilySectionCard>
</template>
