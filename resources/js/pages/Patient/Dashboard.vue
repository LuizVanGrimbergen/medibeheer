<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { useDocumentVisibility } from '@vueuse/core';
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import DailyCheckinCard from '@/Components/Patient/DailyCheckins/form/DailyCheckinCard.vue';
import DailyCheckinSuccessScreen from '@/Components/Patient/DailyCheckins/form/DailyCheckinSuccessScreen.vue';
import TodayMedicationIntakesSection from '@/Components/Patient/Medications/TodayMedicationIntakesSection.vue';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type {
    DailyCheckin,
    DailyMoodScoreValue,
    PageProps,
    TodayMedicationIntakeSlot,
} from '@/lib/types';

const { t } = useI18n();
const page = usePage<PageProps>();

const props = defineProps<{
    today_date: string;
    today_checkin: DailyCheckin | null;
    today_medication_intakes: TodayMedicationIntakeSlot[];
}>();

function localCalendarDateIso(): string {
    const now = new Date();

    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');

    return `${y}-${m}-${d}`;
}

function maybeReloadWhenCalendarDayAdvanced(): void {
    if (localCalendarDateIso() === props.today_date) {
        return;
    }

    router.reload();
}

const documentVisibility = useDocumentVisibility();

watch(documentVisibility, (state) => {
    if (state !== 'visible') {
        return;
    }

    maybeReloadWhenCalendarDayAdvanced();
});

function onWindowPageShow(): void {
    if (document.visibilityState !== 'visible') {
        return;
    }

    maybeReloadWhenCalendarDayAdvanced();
}

onMounted(() => {
    window.addEventListener('pageshow', onWindowPageShow);
});

onUnmounted(() => {
    window.removeEventListener('pageshow', onWindowPageShow);
});

const dailyCheckinMoodFlash = computed((): DailyMoodScoreValue | null => {
    const raw = page.props.flash?.daily_checkin_mood;

    if (raw === 'bad' || raw === 'ok' || raw === 'good') {
        return raw;
    }

    return null;
});
</script>

<template>
    <Head>
        <title>{{ t('patient.dashboard.title') }}</title>
    </Head>

    <PatientLayout>
        <DailyCheckinSuccessScreen :mood="dailyCheckinMoodFlash" />

        <div class="flex flex-col gap-4 sm:gap-8">
            <DailyCheckinCard
                :today_date="props.today_date"
                :today_checkin="props.today_checkin"
            />

            <TodayMedicationIntakesSection :slots="props.today_medication_intakes" />
        </div>
    </PatientLayout>
</template>
