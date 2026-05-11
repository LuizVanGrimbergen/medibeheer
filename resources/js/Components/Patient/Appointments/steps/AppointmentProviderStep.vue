<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { useI18n } from 'vue-i18n';
import { resolveAppointmentDoctorTypeLabel } from '@/Components/Appointments/useAppointmentDisplay';
import {
    appointmentFormFieldInputClass,
    appointmentFormFieldInvalidClass,
    appointmentFormLabelClass,
    appointmentFormSelectBaseClass,
    appointmentFormSelectChevronStyle,
} from '@/Components/Patient/Appointments/appointmentFormFieldClasses';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/appointmentFormTypes';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import type { AppointmentDoctorType } from '@/lib/types';
import { cn } from '@/lib/utils';

const {
    form,
    idPrefix,
    doctorTypeValues,
    showDoctorTypePlaceholder,
} = defineProps<{
    form: AppointmentFormWithErrors;
    idPrefix: string;
    doctorTypeValues: AppointmentDoctorType[];
    showDoctorTypePlaceholder: boolean;
}>();

const { t, te } = useI18n();

function doctorTypeLabel(type: AppointmentDoctorType): string {
    return resolveAppointmentDoctorTypeLabel(t, te, type);
}
</script>

<template>
    <div class="space-y-5 sm:space-y-7">
        <div class="space-y-1 sm:space-y-1.5">
            <p class="daily-checkin-step-title">
                {{ t('patient.appointments.steps.provider.title') }}
            </p>
            <p class="daily-checkin-step-description">
                {{ t('patient.appointments.steps.provider.description') }}
            </p>
        </div>

        <div class="space-y-6">
            <div>
                <Label
                    :for="`${idPrefix}-doctor-type`"
                    :class="appointmentFormLabelClass"
                >
                    {{ t('patient.appointments.fields.doctorType') }}
                </Label>
                <select
                    :id="`${idPrefix}-doctor-type`"
                    v-model="form.doctor_type"
                    aria-required="true"
                    :class="
                        cn(
                            appointmentFormSelectBaseClass,
                            form.errors.doctor_type
                                ? appointmentFormFieldInvalidClass
                                : null,
                        )
                    "
                    :style="appointmentFormSelectChevronStyle"
                    :aria-invalid="Boolean(form.errors.doctor_type)"
                    :aria-describedby="
                        form.errors.doctor_type
                            ? `${idPrefix}-doctor-type-error`
                            : undefined
                    "
                >
                    <option
                        v-if="showDoctorTypePlaceholder"
                        disabled
                        value=""
                    >
                        {{
                            t('patient.appointments.fields.doctorTypePlaceholder')
                        }}
                    </option>
                    <option
                        v-for="opt in doctorTypeValues"
                        :key="opt"
                        :value="opt"
                    >
                        {{ doctorTypeLabel(opt) }}
                    </option>
                </select>
                <InputError
                    :id="`${idPrefix}-doctor-type-error`"
                    :message="form.errors.doctor_type"
                />
            </div>

            <div>
                <Label
                    :for="`${idPrefix}-provider-name`"
                    :class="appointmentFormLabelClass"
                >
                    {{ t('patient.appointments.fields.providerName') }}
                </Label>
                <Input
                    :id="`${idPrefix}-provider-name`"
                    v-model="form.provider_name"
                    type="text"
                    aria-required="true"
                    autocomplete="organization"
                    :class="
                        cn(
                            appointmentFormFieldInputClass,
                            form.errors.provider_name
                                ? appointmentFormFieldInvalidClass
                                : null,
                        )
                    "
                    :placeholder="
                        t('patient.appointments.fields.providerNamePlaceholder')
                    "
                    :aria-invalid="Boolean(form.errors.provider_name)"
                    :aria-describedby="
                        form.errors.provider_name
                            ? `${idPrefix}-provider-name-error`
                            : undefined
                    "
                />
                <InputError
                    :id="`${idPrefix}-provider-name-error`"
                    :message="form.errors.provider_name"
                />
            </div>
        </div>
    </div>
</template>
