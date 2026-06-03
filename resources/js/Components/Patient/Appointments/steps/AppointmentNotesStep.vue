<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { useI18n } from 'vue-i18n';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/form/AppointmentFormTypes';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    patientFormFieldInputClass,
    patientFormFieldInvalidClass,
} from '@/lib/patient/patientFormFieldClasses';
import { cn } from '@/lib/utils';

const { form, idPrefix } = defineProps<{
    form: AppointmentFormWithErrors;
    idPrefix: string;
}>();

const { t } = useI18n();
</script>

<template>
    <div class="space-y-5 sm:space-y-7">
        <div class="space-y-1 sm:space-y-1.5">
            <p class="daily-checkin-step-title">
                {{ t('patient.appointments.steps.notes.title') }}
            </p>
            <p class="daily-checkin-step-description">
                {{ t('patient.appointments.steps.notes.description') }}
            </p>
        </div>

        <div class="space-y-2 sm:space-y-3">
            <Label :for="`${idPrefix}-notes`" class="daily-checkin-step-label">
                {{ t('patient.appointments.fields.notes') }}
            </Label>
            <textarea
                :id="`${idPrefix}-notes`"
                v-model="form.notes"
                rows="4"
                :class="
                    cn(
                        patientFormFieldInputClass,
                        form.errors.notes ? patientFormFieldInvalidClass : null,
                    )
                "
                :placeholder="t('patient.appointments.fields.notesPlaceholder')"
                :aria-invalid="Boolean(form.errors.notes)"
                :aria-describedby="
                    form.errors.notes ? `${idPrefix}-notes-error` : undefined
                "
            />
            <InputError
                :id="`${idPrefix}-notes-error`"
                :message="form.errors.notes"
            />
        </div>
    </div>
</template>
