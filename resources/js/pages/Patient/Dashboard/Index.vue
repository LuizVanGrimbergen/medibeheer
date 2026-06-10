<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import DailyCheckinCard from '@/Components/Patient/DailyCheckins/form/DailyCheckinCard.vue';
import DailyCheckinSuccessScreen from '@/Components/Patient/DailyCheckins/form/DailyCheckinSuccessScreen.vue';
import PatientMedicationOnboardingShortcuts from '@/Components/Patient/Medications/PatientMedicationOnboardingShortcuts.vue';
import PatientMedicationReminderPrompt from '@/Components/Patient/Medications/PatientMedicationReminderPrompt.vue';
import TodayMedicationIntakesSection from '@/Components/Patient/Medications/TodayMedicationIntakesSection.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import ListCardSkeleton from '@/Components/ui/skeleton/ListCardSkeleton.vue';
import { usePatientDashboardPage } from '@/composables/patient/usePatientDashboardPage';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type { PatientDashboardScreenProps } from '@/lib/patient/dashboard/patientDashboardScreenProps';

const { t } = useI18n();

const props = withDefaults(defineProps<PatientDashboardScreenProps>(), {
    has_medications: false,
    can_create_medication: false,
});

const {
    isTodayCheckinLoading,
    isTodayMedicationIntakesLoading,
    showMedicationOnboardingShortcuts,
    dailyCheckinMoodFlash,
    showDailyCheckinCard,
    dailyCheckinEncouragementFlash,
} = usePatientDashboardPage(props);
</script>

<template>
    <Head>
        <title>{{ t('patient.dashboard.title') }}</title>
        <meta
            name="description"
            :content="t('patient.dashboard.metaDescription')"
        />
    </Head>

    <PatientLayout>
        <PatientPageShell :title="t('patient.dashboard.heading')">
            <DailyCheckinSuccessScreen
                :mood="dailyCheckinMoodFlash"
                :message="dailyCheckinEncouragementFlash"
            />

            <DailyCheckinCard
                v-if="showDailyCheckinCard"
                :today_date="props.today_date"
                :today_checkin="null"
            />

            <PatientMedicationOnboardingShortcuts
                v-if="showMedicationOnboardingShortcuts"
                :can-create-medication="props.can_create_medication"
            />

            <PatientMedicationReminderPrompt />

            <ListCardSkeleton
                v-if="isTodayMedicationIntakesLoading"
                class="mt-auto"
            />

            <TodayMedicationIntakesSection
                v-else
                class="mt-auto"
                :slots="props.today_medication_intakes ?? []"
            />
        </PatientPageShell>
    </PatientLayout>
</template>
