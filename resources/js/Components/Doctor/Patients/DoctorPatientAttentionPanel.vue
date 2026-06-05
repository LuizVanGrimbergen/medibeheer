<script setup lang="ts">
import { AlertTriangle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import DoctorCollapsibleSection from '@/Components/Doctor/Patients/DoctorCollapsibleSection.vue';
import {
    buildDoctorPatientAttentionItems
    
} from '@/lib/doctor/patients/buildDoctorPatientAttentionItems';
import type {DoctorPatientAttentionItem} from '@/lib/doctor/patients/buildDoctorPatientAttentionItems';
import { formatDoctorPatientShortDate } from '@/lib/doctor/patients/formatDoctorPatientShortDate';
import { dailyMoodPresentation } from '@/lib/mood/dailyMoodPresentation';
import type { MedicationIntakeCalendarDay } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import type { DailyCheckin } from '@/lib/types';

const props = defineProps<{
    medicationCalendarDays: MedicationIntakeCalendarDay[];
    wellbeingCalendarCheckins: DailyCheckin[];
}>();

const { t, locale } = useI18n();

const isOpen = ref(false);

const items = computed((): DoctorPatientAttentionItem[] =>
    buildDoctorPatientAttentionItems(
        props.medicationCalendarDays,
        props.wellbeingCalendarCheckins,
    ),
);

const collapsedSummary = computed((): string => {
    const count = items.value.length;

    if (count === 1) {
        const firstItem = items.value[0];

        if (firstItem !== undefined) {
            return itemLabel(firstItem);
        }
    }

    return t('doctor.patients.attentionCollapsedMany', {
        count: String(count),
    });
});

function itemLabel(item: DoctorPatientAttentionItem): string {
    const date = formatDoctorPatientShortDate(item.date, locale.value);

    if (item.kind === 'medication') {
        if (item.status === 'none_taken') {
            return t('doctor.patients.attentionMedicationNone', { date });
        }

        if (item.missedCount === 1) {
            return t('doctor.patients.attentionMedicationPartialOne', {
                date,
            });
        }

        return t('doctor.patients.attentionMedicationPartialMany', {
            date,
            count: String(item.missedCount),
        });
    }

    const moodLabel = t(dailyMoodPresentation(item.mood).labelKey);

    if (item.mood === 'bad' && item.symptomCount > 0) {
        return t('doctor.patients.attentionWellbeingBadSymptoms', {
            date,
            mood: moodLabel,
        });
    }

    if (item.mood === 'bad') {
        return t('doctor.patients.attentionWellbeingBad', {
            date,
            mood: moodLabel,
        });
    }

    if (item.symptomCount === 1) {
        return t('doctor.patients.attentionWellbeingSymptomsOne', {
            date,
            mood: moodLabel,
        });
    }

    return t('doctor.patients.attentionWellbeingSymptomsMany', {
        date,
        mood: moodLabel,
        count: String(item.symptomCount),
    });
}
</script>

<template>
    <DoctorCollapsibleSection
        v-if="items.length > 0"
        v-model:open="isOpen"
        :heading="t('doctor.patients.attentionHeading')"
        :toggle-label="t('doctor.patients.attentionToggle')"
        :collapsed-summary="collapsedSummary"
        icon-wrapper-class="bg-danger/12 text-danger"
    >
        <template #icon>
            <AlertTriangle class="size-5" />
        </template>

        <ul class="space-y-2">
            <li
                v-for="item in items"
                :key="`${item.kind}-${item.date}`"
                class="text-text text-sm leading-relaxed"
            >
                {{ itemLabel(item) }}
            </li>
        </ul>
    </DoctorCollapsibleSection>
</template>
