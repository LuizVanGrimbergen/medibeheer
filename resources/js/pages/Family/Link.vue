<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import FamilyIncomingPatientInvitationsSection from '@/Components/Family/Link/FamilyIncomingPatientInvitationsSection.vue';
import FamilyMedicationPlansOverviewSection from '@/Components/Family/Overview/FamilyMedicationPlansOverviewSection.vue';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyMedicationPlanProposalSummary } from '@/lib/family/medicationPlans/familyMedicationPlanProposalSummary';
import type { FamilyDashboardProps, IncomingFamilyInvitation, PageProps } from '@/lib/types';

type PageWithFamily = PageProps & { family?: FamilyDashboardProps };

withDefaults(
    defineProps<{
        proposals: FamilyMedicationPlanProposalSummary[];
        incoming_invitations?: IncomingFamilyInvitation[];
    }>(),
    {
        incoming_invitations: () => [],
    },
);

const { t } = useI18n();
const page = usePage<PageWithFamily>();

const family = computed(() => page.props.family);
</script>

<template>
    <Head>
        <title>{{ t('family.link.title') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyPageShell
            :title="t('family.link.heading')"
            :family="family"
            linked-patients-heading-key="family.link.patientsHeading"
            linked-patients-toggle-key="family.link.patientsToggle"
        >
            <FamilyIncomingPatientInvitationsSection
                :invitations="incoming_invitations"
            />

            <FamilyMedicationPlansOverviewSection
                :proposals="proposals"
                hide-view-all
            />
        </FamilyPageShell>
    </FamilyLayout>
</template>
