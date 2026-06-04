<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/form/AppointmentFormTypes';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { localCalendarDateIsoToday } from '@/lib/patient/appointments/validation/appointmentStartsAtLocalValidation';
import {
    patientFormFieldInvalidClass,
    patientFormNativeDateTimeInputClass,
} from '@/lib/patient/patientFormFieldClasses';
import { cn } from '@/lib/utils';

const { form, idPrefix, startsAtDateInputMinIso } = defineProps<{
    form: AppointmentFormWithErrors;
    idPrefix: string;
    startsAtDateInputMinIso?: string | null;
}>();

const { t } = useI18n();

const pad2 = (n: number) => String(n).padStart(2, '0');

const minStartsAtTimeHm = computed(() => {
    const todayIso = localCalendarDateIsoToday();

    if (form.starts_at_date.trim() !== todayIso) {
        return undefined;
    }

    const now = new Date();

    return `${pad2(now.getHours())}:${pad2(now.getMinutes())}`;
});
</script>

<template>
    <div class="space-y-5 sm:space-y-7">
        <div class="space-y-1 sm:space-y-1.5">
            <p class="daily-checkin-step-title">
                {{ t('patient.appointments.steps.schedule.title') }}
            </p>
        </div>

        <fieldset class="space-y-5 border-0 p-0">
            <legend class="sr-only">
                {{ t('patient.appointments.fields.startsAtGroupLegend') }}
            </legend>
            <div class="space-y-5">
                <div>
                    <Label
                        :for="`${idPrefix}-starts-at-date`"
                        class="text-text-heading mb-2 block text-xl leading-snug font-semibold"
                    >
                        {{ t('patient.appointments.fields.startsAtDate') }}
                        <span class="text-danger">*</span>
                    </Label>
                    <input
                        :id="`${idPrefix}-starts-at-date`"
                        v-model="form.starts_at_date"
                        type="date"
                        :min="startsAtDateInputMinIso ?? undefined"
                        aria-required="true"
                        autocomplete="off"
                        :class="
                            cn(
                                patientFormNativeDateTimeInputClass,
                                form.errors.starts_at
                                    ? patientFormFieldInvalidClass
                                    : null,
                            )
                        "
                        :aria-invalid="Boolean(form.errors.starts_at)"
                        :aria-describedby="
                            form.errors.starts_at
                                ? `${idPrefix}-starts-at-error`
                                : undefined
                        "
                    />
                </div>
                <div>
                    <Label
                        :for="`${idPrefix}-starts-at-time`"
                        class="text-text-heading mb-2 block text-xl leading-snug font-semibold"
                    >
                        {{ t('patient.appointments.fields.startsAtTime') }}
                        <span class="text-danger">*</span>
                    </Label>
                    <input
                        :id="`${idPrefix}-starts-at-time`"
                        v-model="form.starts_at_time"
                        type="time"
                        :min="minStartsAtTimeHm"
                        step="60"
                        aria-required="true"
                        autocomplete="off"
                        :class="
                            cn(
                                patientFormNativeDateTimeInputClass,
                                form.errors.starts_at
                                    ? patientFormFieldInvalidClass
                                    : null,
                            )
                        "
                        :aria-invalid="Boolean(form.errors.starts_at)"
                        :aria-describedby="
                            form.errors.starts_at
                                ? `${idPrefix}-starts-at-error`
                                : undefined
                        "
                    />
                </div>
            </div>
            <InputError
                :id="`${idPrefix}-starts-at-error`"
                :message="form.errors.starts_at"
            />
        </fieldset>
    </div>
</template>
