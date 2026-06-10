<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientFamilyMedicationPlansOverviewSection from '@/Components/Patient/Family/PatientFamilyMedicationPlansOverviewSection.vue';
import PatientFamilyDoctorsSection from '@/Components/Patient/Family/PatientFamilyDoctorsSection.vue';
import PatientFamilyMembersSection from '@/Components/Patient/Family/PatientFamilyMembersSection.vue';
import PatientFamilyPendingMedicationPlansSection from '@/Components/Patient/Family/PatientFamilyPendingMedicationPlansSection.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import ListCardSkeleton from '@/Components/ui/skeleton/ListCardSkeleton.vue';
import { usePatientFamilyPage } from '@/composables/patient/usePatientFamilyPage';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type { PatientFamilyScreenProps } from '@/lib/patient/family/patientFamilyScreenProps';

const props = defineProps<PatientFamilyScreenProps>();

const { t } = useI18n();

const { isCareTeamLoading } = usePatientFamilyPage(props);

const hasPendingMedicationPlans = computed(
    () => (props.pending_medication_plans ?? []).length > 0,
);
</script>

<template>
    <Head>
        <title>{{ t('patient.family.title') }}</title>
        <meta
            name="description"
            :content="t('patient.family.metaDescription')"
        />
    </Head>

    <PatientLayout>
        <PatientPageShell :title="t('patient.family.heading')">
            <div
                class="flex flex-col gap-6 md:gap-5"
                :aria-busy="isCareTeamLoading"
            >
                <ListCardSkeleton v-if="isCareTeamLoading" />

                <template v-else>
                    <PatientFamilyPendingMedicationPlansSection
                        :pending-medication-plans="
                            props.pending_medication_plans ?? []
                        "
                    />

                    <PatientFamilyMembersSection
                        :linked-family-members="
                            props.linked_family_members ?? []
                        "
                        :family-invitations="props.family_invitations ?? []"
                        :family-invitation-store-url="
                            props.family_invitation_store_url ?? ''
                        "
                    />

                    <PatientFamilyDoctorsSection
                        :linked-doctors="props.linked_doctors ?? []"
                        :doctor-invitations="props.doctor_invitations ?? []"
                        :doctor-invitation-store-url="
                            props.doctor_invitation_store_url ?? ''
                        "
                    />

                    <PatientFamilyMedicationPlansOverviewSection
                        :has-pending-medication-plans="hasPendingMedicationPlans"
                        :accepted-medication-plans="
                            props.accepted_medication_plans ?? []
                        "
                    />
                </template>
            </div>
        </PatientPageShell>
    </PatientLayout>
</template>
