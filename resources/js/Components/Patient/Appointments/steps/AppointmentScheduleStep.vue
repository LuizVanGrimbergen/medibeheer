<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    appointmentFormFieldInvalidClass,
    appointmentFormNativeDateTimeInputClass,
} from '@/Components/Patient/Appointments/appointmentFormFieldClasses';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/appointmentFormTypes';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { cn } from '@/lib/utils';
import {
    localCalendarDateIsoToday,
} from '../../../../lib/patient/appointments/appointmentStartsAtLocalValidation';

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
            <p class="daily-checkin-step-description">
                {{ t('patient.appointments.steps.schedule.description') }}
            </p>
        </div>

        <fieldset class="space-y-5 border-0 p-0">
            <legend
                class="sr-only"
            >
                {{ t('patient.appointments.fields.startsAtGroupLegend') }}
            </legend>
            <p
                :id="`${idPrefix}-starts-at-hint`"
                class="text-lg leading-relaxed text-text-muted"
            >
                {{ t('patient.appointments.fields.startsAtHint') }}
            </p>
            <div class="space-y-5">
                <div>
                    <Label
                        :for="`${idPrefix}-starts-at-date`"
                        class="mb-2 block text-xl font-semibold leading-snug text-text-heading"
                    >
                        {{ t('patient.appointments.fields.startsAtDate') }}
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
                                appointmentFormNativeDateTimeInputClass,
                                form.errors.starts_at
                                    ? appointmentFormFieldInvalidClass
                                    : null,
                            )
                        "
                        :aria-invalid="Boolean(form.errors.starts_at)"
                        :aria-describedby="
                            form.errors.starts_at
                                ? `${idPrefix}-starts-at-hint ${idPrefix}-starts-at-error`
                                : `${idPrefix}-starts-at-hint`
                        "
                    />
                </div>
                <div>
                    <Label
                        :for="`${idPrefix}-starts-at-time`"
                        class="mb-2 block text-xl font-semibold leading-snug text-text-heading"
                    >
                        {{ t('patient.appointments.fields.startsAtTime') }}
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
                                appointmentFormNativeDateTimeInputClass,
                                form.errors.starts_at
                                    ? appointmentFormFieldInvalidClass
                                    : null,
                            )
                        "
                        :aria-invalid="Boolean(form.errors.starts_at)"
                        :aria-describedby="
                            form.errors.starts_at
                                ? `${idPrefix}-starts-at-hint ${idPrefix}-starts-at-error`
                                : `${idPrefix}-starts-at-hint`
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
