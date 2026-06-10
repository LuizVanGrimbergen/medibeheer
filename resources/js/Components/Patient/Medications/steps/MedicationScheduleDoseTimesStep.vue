<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { formatMedicationSnoozeMinutesLabel } from '@/lib/patient/medications/schedule/formatMedicationSnoozeLabel';
import {
    MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES,
    MEDICATION_SCHEDULE_SNOOZE_MINUTE_OPTIONS,
} from '@/lib/patient/medications/schedule/medicationScheduleDoseTimes';
import { parseMedicationTimesPerDayCount } from '@/lib/patient/medications/validation/medicationFormValidationPrimitives';
import {
    mobileShellFormFieldInvalidClass,
    mobileShellFormLabelClass,
    mobileShellFormNativeDateTimeInputClass,
    mobileShellFormSelectBaseClass,
    mobileShellFormSelectChevronStyle,
} from '@/lib/shell/mobileShellFormFieldClasses';
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

function ensureSnoozeSlotsForVisibleIndices(): void {
    const indices = doseTimeSlotIndices.value;
    const current = form.schedule.snooze_time_slots;
    let next: string[] | null = null;

    for (const index of indices) {
        if (current[index] !== undefined) {
            continue;
        }

        if (next === null) {
            next = [...current];
        }

        next[index] = String(MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES);
    }

    if (next !== null) {
        form.schedule.snooze_time_slots = next;
    }
}

function setSnoozeSlot(index: number, minutes: string): void {
    const next = [...form.schedule.snooze_time_slots];
    next[index] = minutes;
    form.schedule.snooze_time_slots = next;
}

watch(doseTimeSlotIndices, ensureSnoozeSlotsForVisibleIndices, {
    immediate: true,
});
</script>

<template>
    <fieldset class="mt-0 border-0 p-0">
        <legend
            :id="`${idPrefix}-schedule-dose-times-label`"
            :class="cn(mobileShellFormLabelClass, 'float-none w-full px-0 text-xl')"
        >
            {{ t('patient.medications.fields.doseTime') }}
            <span class="text-danger">*</span>
        </legend>
        <div class="mt-4 space-y-4">
            <div
                v-for="index in doseTimeSlotIndices"
                :key="index"
                class="border-border/60 bg-muted/30 space-y-3 rounded-2xl border p-4 md:rounded-3xl md:p-5"
            >
                <p class="text-text-heading text-sm font-semibold">
                    {{ t('patient.medications.fields.doseTime') }}
                    {{ index + 1 }}
                </p>
                <div class="space-y-2">
                    <Label
                        :for="`${idPrefix}-schedule-dose-time-${index}`"
                        :class="mobileShellFormLabelClass"
                    >
                        {{ t('patient.medications.fields.doseTimeAt') }}
                        <span class="text-danger">*</span>
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
                                mobileShellFormNativeDateTimeInputClass,
                                form.errors['schedule.dose_time']
                                    ? mobileShellFormFieldInvalidClass
                                    : null,
                            )
                        "
                        :aria-invalid="
                            Boolean(form.errors['schedule.dose_time'])
                        "
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
                        :class="mobileShellFormLabelClass"
                    >
                        {{ t('patient.medications.fields.snoozeTime') }}
                        <span class="text-danger">*</span>
                    </Label>
                    <select
                        :id="`${idPrefix}-schedule-snooze-time-${index}`"
                        :value="
                            form.schedule.snooze_time_slots[index] ??
                            String(MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES)
                        "
                        :class="
                            cn(
                                mobileShellFormSelectBaseClass,
                                mobileShellFormSelectChevronStyle,
                            )
                        "
                        :aria-invalid="
                            Boolean(form.errors['schedule.snooze_time'])
                        "
                        @change="
                            setSnoozeSlot(
                                index,
                                ($event.target as HTMLSelectElement).value,
                            )
                        "
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
            :message="
                form.errors['schedule.dose_time'] ??
                form.errors['schedule.snooze_time']
            "
        />
    </fieldset>
</template>
