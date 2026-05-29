<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import FamilyIncomingPatientInvitationsSection from '@/Components/Family/Link/FamilyIncomingPatientInvitationsSection.vue';
import FamilyPatientsLinkSection from '@/Components/Family/Link/FamilyPatientsLinkSection.vue';
import FamilyMedicationPlansOverviewSection from '@/Components/Family/Overview/FamilyMedicationPlansOverviewSection.vue';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyMedicationPlanProposalSummary } from '@/lib/family/medicationPlans/familyMedicationPlanProposalSummary';
import type { IncomingFamilyInvitation } from '@/lib/types';

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
</script>

<template>
    <Head>
        <title>{{ t('family.link.title') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyPageShell :title="t('family.link.heading')">
            <FamilyPatientsLinkSection />

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
