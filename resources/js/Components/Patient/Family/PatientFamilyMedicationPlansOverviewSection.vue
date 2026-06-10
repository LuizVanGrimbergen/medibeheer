<script setup lang="ts">
import { ClipboardList } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientFamilySectionCard from '@/Components/Patient/Family/PatientFamilySectionCard.vue';
import CollapsibleSectionCard from '@/Components/ui/collapsible-section/CollapsibleSectionCard.vue';
import { formatCareTeamExpiry } from '@/lib/patient/careTeam/formatCareTeamExpiry';
import {
    mobileShellSectionBodyTextClass,
    mobileShellSectionHeadingClass,
    mobileShellSectionSubHeadingClass,
} from '@/lib/shell/mobileShellTypography';
import type { AcceptedMedicationPlanProposal } from '@/lib/types';
import { cn } from '@/lib/utils';

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

const acceptedMedicationPlansOpen = ref(false);

const acceptedPlansCollapsedSummary = computed((): string => {
    if (props.acceptedMedicationPlans.length === 1) {
        return t('patient.family.acceptedMedicationPlansCollapsedOne');
    }

    return t('patient.family.acceptedMedicationPlansCollapsedMany', {
        count: String(props.acceptedMedicationPlans.length),
    });
});

const acceptedPlansToggleLabel = computed((): string =>
    t(
        acceptedMedicationPlansOpen.value
            ? 'patient.family.hideDetails'
            : 'patient.family.showDetails',
    ),
);
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

        <div v-if="props.acceptedMedicationPlans.length > 0" class="mt-8">
            <CollapsibleSectionCard
                v-model:open="acceptedMedicationPlansOpen"
                :heading="t('patient.family.acceptedMedicationPlansHeading')"
                :toggle-label="acceptedPlansToggleLabel"
                :expand-label="t('patient.family.listExpandHint')"
                :collapse-label="t('patient.family.listCollapseHint')"
                toggle-variant="footer-button"
                :collapsed-summary="acceptedPlansCollapsedSummary"
                icon-wrapper-class="bg-primary/10 text-primary"
                content-class="border-border flex flex-col border-t px-4 pb-4 pt-4 md:px-5 md:pb-5 md:pt-4"
            >
                <template #icon>
                    <ClipboardList aria-hidden="true" />
                </template>

                <p :class="mobileShellSectionBodyTextClass">
                    {{ t('patient.family.acceptedMedicationPlansIntro') }}
                </p>

                <ul class="mt-4 flex flex-col gap-0">
                    <li
                        v-for="plan in props.acceptedMedicationPlans"
                        :key="plan.id"
                        :class="
                            cn(
                                'border-border border-t pt-4 first:border-t-0 first:pt-0',
                            )
                        "
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
                                    t(
                                        'patient.family.acceptedMedicationPlansBy',
                                        {
                                            name: plan.family_member_name,
                                        },
                                    )
                                }}
                            </p>
                            <p
                                v-if="plan.accepted_at !== null"
                                class="text-text-muted text-sm md:text-base"
                            >
                                {{
                                    t('patient.family.acceptedAt', {
                                        date: formatCareTeamExpiry(
                                            plan.accepted_at,
                                        ),
                                    })
                                }}
                            </p>
                        </div>
                    </li>
                </ul>
            </CollapsibleSectionCard>
        </div>
    </PatientFamilySectionCard>
</template>
