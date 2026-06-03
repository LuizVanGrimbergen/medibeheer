<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAppointmentDisplay } from '@/Components/Appointments/useAppointmentDisplay';
import MedicationListCardLead from '@/Components/Medications/MedicationListCardLead.vue';
import {
    medicationIntakeDoseLine,
} from '@/lib/patient/medications/display/medicationIntakeSlotDisplay';
import { patientPageCardHeaderSummaryClass } from '@/lib/patient/patientPageTypography';
import type { MedicationTypeValue, TodayMedicationIntakeSlot } from '@/lib/types';

const props = defineProps<{
    intakeSlot: TodayMedicationIntakeSlot;
}>();

const { t } = useI18n();
const { formatTimeOnly } = useAppointmentDisplay();

const doseLine = computed(() => medicationIntakeDoseLine(t, props.intakeSlot));

const takenTimeLabel = computed((): string => {
    const takenAt = props.intakeSlot.taken_at?.trim();

    if (takenAt !== undefined && takenAt !== null && takenAt.length > 0) {
        return formatTimeOnly(takenAt);
    }

    return props.intakeSlot.dose_time;
});

const summaryLine = computed((): string | null => doseLine.value);

const rowAriaLabel = computed(() =>
    t('patient.dashboard.todayMedications.takenSection.rowAria', {
        name: props.intakeSlot.name,
        time: takenTimeLabel.value,
    }),
);
</script>

<template>
    <div
        class="flex min-w-0 items-center gap-4 py-4 sm:py-4.5"
        :aria-label="rowAriaLabel"
    >
        <MedicationListCardLead
            class="min-w-0 flex-1"
            :name="intakeSlot.name"
            :type-medication="intakeSlot.type_medication as MedicationTypeValue"
            :tone="null"
            :show-type-label="false"
        >
            <template v-if="summaryLine !== null" #subtitle>
                <p :class="patientPageCardHeaderSummaryClass">
                    {{ summaryLine }}
                </p>
            </template>
        </MedicationListCardLead>

        <time
            :datetime="intakeSlot.taken_at ?? undefined"
            class="text-text-muted shrink-0 text-sm font-semibold tabular-nums sm:text-base"
        >
            {{ takenTimeLabel }}
        </time>
    </div>
</template>
