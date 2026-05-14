<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { parseMedicationTimesPerDayCount } from '@/lib/patient/medications/validation/medicationFormValidationPrimitives';
import {
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormNativeDateTimeInputClass,
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
                class="space-y-2"
            >
                <Label
                    :for="`${idPrefix}-schedule-dose-time-${index}`"
                    class="sr-only"
                >
                    {{ t('patient.medications.fields.doseTime') }} {{ index + 1 }}
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
        </div>
        <InputError
            :id="`${idPrefix}-schedule-dose-time-error`"
            :message="form.errors['schedule.dose_time']"
        />
    </fieldset>
</template>
