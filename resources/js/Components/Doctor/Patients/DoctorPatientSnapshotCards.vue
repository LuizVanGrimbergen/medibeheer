<script setup lang="ts">
import { Heart, Pill } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import HistoryMonthNavigation from '@/Components/History/HistoryMonthNavigation.vue';
import { MoodIcon } from '@/Components/ui/mood-icon';
import {
    buildDoctorPatientOverviewSnapshot,
    type DoctorPatientOverviewSnapshot,
} from '@/lib/doctor/patients/buildDoctorPatientOverviewSnapshot';
import {
    medicationIntakeDayPresentation,
    type MedicationIntakeDayIconStatusValue,
    type MedicationIntakeDayPresentation,
} from '@/lib/patient/medications/history/medicationIntakeDayPresentation';
import {
    dailyMoodPresentation,
    type DailyMoodPresentation,
} from '@/lib/mood/dailyMoodPresentation';
import type { MedicationIntakeCalendarDay } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import type { DailyCheckin, DailyMoodScoreValue } from '@/lib/types';
import { cn } from '@/lib/utils';
import { MedicationIntakeDayIcon } from '@/Components/ui/medication-intake-day-icon';

const props = withDefaults(
    defineProps<{
        calendarMonth: string;
        medicationCalendarDays: MedicationIntakeCalendarDay[];
        wellbeingCalendarCheckins: DailyCheckin[];
        selectedMoodFilter?: DailyMoodScoreValue | null;
        selectedMedicationStatusFilter?: MedicationIntakeDayIconStatusValue | null;
    }>(),
    {
        selectedMoodFilter: null,
        selectedMedicationStatusFilter: null,
    },
);

const emit = defineEmits<{
    selectMood: [mood: DailyMoodScoreValue];
    selectMedicationStatus: [status: MedicationIntakeDayIconStatusValue];
}>();

const { t } = useI18n();

const snapshot = computed((): DoctorPatientOverviewSnapshot =>
    buildDoctorPatientOverviewSnapshot(
        props.medicationCalendarDays,
        props.wellbeingCalendarCheckins,
    ),
);

const MEDICATION_STATUS_DISPLAY_ORDER: MedicationIntakeDayIconStatusValue[] = [
    'complete',
    'partial',
    'none_taken',
];

const medicationStatusBreakdown = computed(
    (): MedicationIntakeDayPresentation[] =>
        MEDICATION_STATUS_DISPLAY_ORDER.map((status) =>
            medicationIntakeDayPresentation(status),
        ),
);

const WELLBEING_MOOD_DISPLAY_ORDER: DailyMoodScoreValue[] = [
    'good',
    'ok',
    'bad',
];

const wellbeingMoodBreakdown = computed((): DailyMoodPresentation[] =>
    WELLBEING_MOOD_DISPLAY_ORDER.map((mood) => dailyMoodPresentation(mood)),
);
</script>

<template>
    <div class="flex min-w-0 flex-col gap-3">
        <HistoryMonthNavigation
            :calendar-month="props.calendarMonth"
            navigate-route-name="doctor.dashboard"
            density="compact"
            :prev-month-aria-label="t('doctor.patients.snapshotPrevMonth')"
            :next-month-aria-label="t('doctor.patients.snapshotNextMonth')"
        />

        <div class="grid min-w-0 gap-4 md:grid-cols-2 md:gap-5">
        <article
            class="border-border bg-surface flex min-w-0 flex-col gap-3 rounded-2xl border px-5 py-4 shadow-sm md:px-6 md:py-5"
        >
            <div class="flex items-center gap-3">
                <div
                    class="bg-role-doctor/12 text-role-doctor flex size-10 shrink-0 items-center justify-center rounded-full"
                    aria-hidden="true"
                >
                    <Pill class="size-5" />
                </div>
                <h2 class="text-text-heading text-base font-semibold">
                    {{ t('doctor.patients.snapshotMedicationHeading') }}
                </h2>
            </div>

            <p
                v-if="snapshot.medication.scheduledDays === 0"
                class="text-text-muted text-xl font-semibold md:text-2xl"
            >
                {{ t('doctor.patients.snapshotAdherenceEmpty') }}
            </p>

            <ul
                v-else
                class="grid min-w-0 grid-cols-3 gap-2 md:gap-3"
            >
                <li
                    v-for="option in medicationStatusBreakdown"
                    :key="option.status"
                >
                    <button
                        type="button"
                        class="w-full"
                        :disabled="
                            snapshot.medication.statusCounts[option.status] ===
                            0
                        "
                        :class="
                            cn(
                                'flex min-w-0 flex-col items-center gap-1.5 rounded-xl px-2 py-3 text-center transition-shadow',
                                option.iconBackgroundClass,
                                snapshot.medication.statusCounts[option.status] >
                                    0 &&
                                    'hover:ring-border cursor-pointer hover:ring-2 hover:ring-offset-2',
                                props.selectedMedicationStatusFilter ===
                                    option.status && 'ring-2 ring-offset-2',
                                props.selectedMedicationStatusFilter ===
                                    option.status &&
                                    option.status === 'complete' &&
                                    'ring-success',
                                props.selectedMedicationStatusFilter ===
                                    option.status &&
                                    option.status === 'partial' &&
                                    'ring-warning',
                                props.selectedMedicationStatusFilter ===
                                    option.status &&
                                    option.status === 'none_taken' &&
                                    'ring-danger',
                                snapshot.medication.statusCounts[option.status] ===
                                    0 && 'cursor-default opacity-60',
                            )
                        "
                        :aria-label="
                            t('doctor.patients.snapshotMedicationStatusSelect', {
                                status: t(option.labelKey),
                            })
                        "
                        :aria-pressed="
                            props.selectedMedicationStatusFilter ===
                            option.status
                        "
                        @click="emit('selectMedicationStatus', option.status)"
                    >
                        <MedicationIntakeDayIcon
                            :status="option.status"
                            size="card"
                        />
                        <span
                            :class="
                                cn(
                                    'text-xl font-semibold tabular-nums leading-none md:text-2xl',
                                    option.faceClass,
                                )
                            "
                        >
                            {{
                                snapshot.medication.statusCounts[
                                    option.status
                                ]
                            }}
                        </span>
                        <span
                            class="text-text-muted text-xs leading-tight font-medium"
                        >
                            {{ t(option.labelKey) }}
                        </span>
                    </button>
                </li>
            </ul>
        </article>

        <article
            class="border-border bg-surface flex min-w-0 flex-col gap-3 rounded-2xl border px-5 py-4 shadow-sm md:px-6 md:py-5"
        >
            <div class="flex items-center gap-3">
                <div
                    class="bg-role-doctor/12 text-role-doctor flex size-10 shrink-0 items-center justify-center rounded-full"
                    aria-hidden="true"
                >
                    <Heart class="size-5" />
                </div>
                <h2 class="text-text-heading text-base font-semibold">
                    {{ t('doctor.patients.snapshotWellbeingHeading') }}
                </h2>
            </div>

            <p
                v-if="snapshot.wellbeing.checkinCount === 0"
                class="text-text-muted text-xl font-semibold md:text-2xl"
            >
                {{ t('doctor.patients.snapshotNoCheckins') }}
            </p>

            <ul
                v-else
                class="grid min-w-0 grid-cols-3 gap-2 md:gap-3"
            >
                <li
                    v-for="option in wellbeingMoodBreakdown"
                    :key="option.mood"
                >
                    <button
                        type="button"
                        class="w-full"
                        :disabled="
                            snapshot.wellbeing.moodCounts[option.mood] === 0
                        "
                        :class="
                            cn(
                                'flex min-w-0 flex-col items-center gap-1.5 rounded-xl px-2 py-3 text-center transition-shadow',
                                option.iconBackgroundClass,
                                snapshot.wellbeing.moodCounts[option.mood] > 0 &&
                                    'hover:ring-border cursor-pointer hover:ring-2 hover:ring-offset-2',
                                props.selectedMoodFilter === option.mood &&
                                    'ring-2 ring-offset-2',
                                props.selectedMoodFilter === option.mood &&
                                    option.mood === 'good' &&
                                    'ring-success',
                                props.selectedMoodFilter === option.mood &&
                                    option.mood === 'ok' &&
                                    'ring-warning',
                                props.selectedMoodFilter === option.mood &&
                                    option.mood === 'bad' &&
                                    'ring-danger',
                                snapshot.wellbeing.moodCounts[option.mood] === 0 &&
                                    'cursor-default opacity-60',
                            )
                        "
                        :aria-label="
                            t('doctor.patients.snapshotMoodSelect', {
                                mood: t(option.labelKey),
                            })
                        "
                        :aria-pressed="props.selectedMoodFilter === option.mood"
                        @click="emit('selectMood', option.mood)"
                    >
                        <MoodIcon :mood="option.mood" size="card" />
                        <span
                            :class="
                                cn(
                                    'text-xl font-semibold tabular-nums leading-none md:text-2xl',
                                    option.faceClass,
                                )
                            "
                        >
                            {{
                                snapshot.wellbeing.moodCounts[option.mood]
                            }}
                        </span>
                        <span
                            class="text-text-muted text-xs leading-tight font-medium"
                        >
                            {{ t(option.labelKey) }}
                        </span>
                    </button>
                </li>
            </ul>
        </article>
        </div>
    </div>
</template>
