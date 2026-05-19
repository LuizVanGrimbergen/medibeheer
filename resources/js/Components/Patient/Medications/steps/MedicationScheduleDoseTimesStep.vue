<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { formatMedicationSnoozeMinutesLabel } from '@/lib/patient/medications/schedule/formatMedicationSnoozeLabel';
import {
    MEDICATION_SCHEDULE_SNOOZE_MINUTE_OPTIONS,
    MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES,
} from '@/lib/patient/medications/schedule/medicationScheduleDoseTimes';
import { parseMedicationTimesPerDayCount } from '@/lib/patient/medications/validation/medicationFormValidationPrimitives';
import {
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormNativeDateTimeInputClass,
    patientFormSelectBaseClass,
    patientFormSelectChevronStyle,
} from '@/lib/patient/patientFormFieldClasses';
import { cn } from '@/lib/utils';

const { form, idPrefix } = defineProps<{
    form: MedicationCreateFormWithErrors;
    idPrefix: string;
}>();

const { t } = useI18n();

const doseTimeSlotCount = computed(() => {
    const parsed = parseMedicationTimesPerDayCount(form.schedule.times_per_day);

    if (parsed === null) {
        return 1;
    }

    return parsed;
});

const doseTimeSlotIndices = computed(() =>
    Array.from({ length: doseTimeSlotCount.value }, (_, index) => index),
);

function ensureSnoozeSlot(index: number): void {
    if (form.schedule.snooze_time_slots[index] === undefined) {
        form.schedule.snooze_time_slots[index] = String(MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES);
    }
}
</script>

<template>
    <fieldset class="mt-0 border-0 p-0">
        <legend
            :id="`${idPrefix}-schedule-dose-times-label`"
            :class="cn(patientFormLabelClass, 'float-none w-full px-0 text-xl')"
        >
            {{ t('patient.medications.fields.doseTime') }}
        </legend>
        <div class="mt-4 space-y-4">
            <div
                v-for="index in doseTimeSlotIndices"
                :key="index"
                class="space-y-3 rounded-2xl border border-border/60 bg-muted/30 p-4 md:rounded-3xl md:p-5"
            >
                <p class="text-sm font-semibold text-text-heading">
                    {{ t('patient.medications.fields.doseTime') }} {{ index + 1 }}
                </p>
                <div class="space-y-2">
                    <Label
                        :for="`${idPrefix}-schedule-dose-time-${index}`"
                        :class="patientFormLabelClass"
                    >
                        {{ t('patient.medications.fields.doseTimeAt') }}
                    </Label>
                    <input
                        :id="`${idPrefix}-schedule-dose-time-${index}`"
                        v-model="form.schedule.dose_time_slots[index]"
                        type="time"
                        step="60"
                        aria-required="true"
                        autocomplete="off"
                        :class="
                            cn(
                                patientFormNativeDateTimeInputClass,
                                form.errors['schedule.dose_time']
                                    ? patientFormFieldInvalidClass
                                    : null,
                            )
                        "
                        :aria-invalid="Boolean(form.errors['schedule.dose_time'])"
                        :aria-describedby="
                            form.errors['schedule.dose_time']
                                ? `${idPrefix}-schedule-dose-time-error`
                                : undefined
                        "
                    />
                </div>
                <div class="space-y-2">
                    <Label
                        :for="`${idPrefix}-schedule-snooze-time-${index}`"
                        :class="patientFormLabelClass"
                    >
                        {{ t('patient.medications.fields.snoozeTime') }}
                    </Label>
                    <select
                        :id="`${idPrefix}-schedule-snooze-time-${index}`"
                        v-model="form.schedule.snooze_time_slots[index]"
                        :class="cn(patientFormSelectBaseClass, patientFormSelectChevronStyle)"
                        :aria-invalid="Boolean(form.errors['schedule.snooze_time'])"
                        @focus="ensureSnoozeSlot(index)"
                    >
                        <option
                            v-for="minutes in MEDICATION_SCHEDULE_SNOOZE_MINUTE_OPTIONS"
                            :key="minutes"
                            :value="String(minutes)"
                        >
                            {{ formatMedicationSnoozeMinutesLabel(t, minutes) }}
                        </option>
                    </select>
                </div>
            </div>
        </div>
        <InputError
            :id="`${idPrefix}-schedule-dose-time-error`"
            :message="form.errors['schedule.dose_time'] ?? form.errors['schedule.snooze_time']"
        />
    </fieldset>
</template>
