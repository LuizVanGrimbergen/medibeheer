<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import PatientFamilySectionCard from '@/Components/Patient/Family/PatientFamilySectionCard.vue';
import { formatCareTeamExpiry } from '@/lib/patient/careTeam/formatCareTeamExpiry';
import {
    mobileShellSectionBodyTextClass,
    mobileShellSectionHeadingClass,
    mobileShellSectionSubHeadingClass,
} from '@/lib/shell/mobileShellTypography';
import {
    mobileShellPageSectionInnerRowSuccessClass,
} from '@/lib/shell/mobileShellLayout';
import type { AcceptedMedicationPlanProposal } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        hasPendingMedicationPlans?: boolean;
        acceptedMedicationPlans?: AcceptedMedicationPlanProposal[];
    }>(),
    {
        hasPendingMedicationPlans: false,
        acceptedMedicationPlans: () => [],
    },
);

const { t } = useI18n();
</script>

<template>
    <PatientFamilySectionCard
        v-if="
            props.acceptedMedicationPlans.length > 0 ||
            !props.hasPendingMedicationPlans
        "
        aria-labelledby="family-medication-plans-heading"
    >
        <h2
            id="family-medication-plans-heading"
            :class="mobileShellSectionHeadingClass"
        >
            {{ t('patient.family.medicationPlansHeading') }}
        </h2>

        <p
            v-if="
                !props.hasPendingMedicationPlans &&
                props.acceptedMedicationPlans.length === 0
            "
            class="mt-4"
            :class="mobileShellSectionBodyTextClass"
        >
            {{ t('patient.family.medicationPlansEmpty') }}
        </p>

        <div v-if="props.acceptedMedicationPlans.length > 0" class="mt-6">
            <h3 :class="mobileShellSectionSubHeadingClass">
                {{ t('patient.family.acceptedMedicationPlansHeading') }}
            </h3>
            <p class="mt-2" :class="mobileShellSectionBodyTextClass">
                {{ t('patient.family.acceptedMedicationPlansIntro') }}
            </p>

            <ul class="mt-4 flex flex-col gap-3">
                <li
                    v-for="plan in props.acceptedMedicationPlans"
                    :key="plan.id"
                    :class="mobileShellPageSectionInnerRowSuccessClass"
                >
                    <div class="flex flex-col gap-1">
                        <p
                            class="text-text-heading text-base font-semibold md:text-lg"
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
                                t('patient.family.acceptedMedicationPlansBy', {
                                    name: plan.family_member_name,
                                })
                            }}
                        </p>
                        <p
                            v-if="plan.accepted_at !== null"
                            class="text-text-muted text-sm md:text-base"
                        >
                            {{
                                t('patient.family.acceptedAt', {
                                    date: formatCareTeamExpiry(plan.accepted_at),
                                })
                            }}
                        </p>
                    </div>
                </li>
            </ul>
        </div>
    </PatientFamilySectionCard>
</template>
