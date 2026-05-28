<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import FamilyInvitationAcceptSection from '@/Components/Family/Overview/FamilyInvitationAcceptSection.vue';
import FamilyMedicationPlansOverviewSection from '@/Components/Family/Overview/FamilyMedicationPlansOverviewSection.vue';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyMedicationPlanProposalSummary } from '@/lib/family/medicationPlans/familyMedicationPlanProposalSummary';

defineProps<{
    proposals: FamilyMedicationPlanProposalSummary[];
}>();

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ t('family.link.title') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyPageShell :title="t('family.link.heading')">
            <FamilyInvitationAcceptSection />

            <FamilyMedicationPlansOverviewSection
                :proposals="proposals"
                hide-view-all
            />
        </FamilyPageShell>
    </FamilyLayout>
</template>
