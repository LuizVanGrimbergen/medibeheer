<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { useI18n } from 'vue-i18n';
import {
    appointmentFormFieldInputClass,
    appointmentFormFieldInvalidClass,
    appointmentFormLabelClass,
} from '@/Components/Patient/Appointments/appointmentFormFieldClasses';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/appointmentFormTypes';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
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
                {{ t('patient.appointments.steps.address.title') }}
            </p>
            <p class="daily-checkin-step-description">
                {{ t('patient.appointments.steps.address.description') }}
            </p>
        </div>

        <fieldset class="space-y-5 border-0 p-0">
            <legend
                class="sr-only"
            >
                {{ t('patient.appointments.fields.addressGroupLegend') }}
            </legend>
            <div class="space-y-5">
                <div>
                    <Label
                        :for="`${idPrefix}-street`"
                        :class="appointmentFormLabelClass"
                    >
                        {{ t('patient.appointments.fields.street') }}
                    </Label>
                    <Input
                        :id="`${idPrefix}-street`"
                        v-model="form.street"
                        type="text"
                        aria-required="true"
                        autocomplete="address-line1"
                        :class="
                            cn(
                                appointmentFormFieldInputClass,
                                form.errors.street
                                    ? appointmentFormFieldInvalidClass
                                    : null,
                            )
                        "
                        :placeholder="
                            t('patient.appointments.fields.streetPlaceholder')
                        "
                        :aria-invalid="Boolean(form.errors.street)"
                        :aria-describedby="
                            form.errors.street
                                ? `${idPrefix}-street-error`
                                : undefined
                        "
                    />
                    <InputError
                        :id="`${idPrefix}-street-error`"
                        :message="form.errors.street"
                    />
                </div>
                <div>
                    <Label
                        :for="`${idPrefix}-house-number`"
                        :class="appointmentFormLabelClass"
                    >
                        {{ t('patient.appointments.fields.houseNumber') }}
                    </Label>
                    <Input
                        :id="`${idPrefix}-house-number`"
                        v-model="form.house_number"
                        type="text"
                        autocomplete="off"
                        :class="
                            cn(
                                appointmentFormFieldInputClass,
                                form.errors.house_number
                                    ? appointmentFormFieldInvalidClass
                                    : null,
                            )
                        "
                        :placeholder="
                            t('patient.appointments.fields.houseNumberPlaceholder')
                        "
                        :aria-invalid="Boolean(form.errors.house_number)"
                        :aria-describedby="
                            form.errors.house_number
                                ? `${idPrefix}-house-number-error`
                                : undefined
                        "
                    />
                    <InputError
                        :id="`${idPrefix}-house-number-error`"
                        :message="form.errors.house_number"
                    />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <Label
                            :for="`${idPrefix}-postal-code`"
                            :class="appointmentFormLabelClass"
                        >
                            {{ t('patient.appointments.fields.postalCode') }}
                        </Label>
                        <Input
                            :id="`${idPrefix}-postal-code`"
                            v-model="form.postal_code"
                            type="text"
                            aria-required="true"
                            maxlength="32"
                            autocomplete="postal-code"
                            :class="
                                cn(
                                    appointmentFormFieldInputClass,
                                    form.errors.postal_code
                                        ? appointmentFormFieldInvalidClass
                                        : null,
                                )
                            "
                            :placeholder="
                                t('patient.appointments.fields.postalCodePlaceholder')
                            "
                            :aria-invalid="Boolean(form.errors.postal_code)"
                            :aria-describedby="
                                form.errors.postal_code
                                    ? `${idPrefix}-postal-code-error`
                                    : undefined
                            "
                        />
                        <InputError
                            :id="`${idPrefix}-postal-code-error`"
                            :message="form.errors.postal_code"
                        />
                    </div>
                    <div>
                        <Label
                            :for="`${idPrefix}-city`"
                            :class="appointmentFormLabelClass"
                        >
                            {{ t('patient.appointments.fields.city') }}
                        </Label>
                        <Input
                            :id="`${idPrefix}-city`"
                            v-model="form.city"
                            type="text"
                            aria-required="true"
                            autocomplete="address-level2"
                            :class="
                                cn(
                                    appointmentFormFieldInputClass,
                                    form.errors.city
                                        ? appointmentFormFieldInvalidClass
                                        : null,
                                )
                            "
                            :placeholder="
                                t('patient.appointments.fields.cityPlaceholder')
                            "
                            :aria-invalid="Boolean(form.errors.city)"
                            :aria-describedby="
                                form.errors.city
                                    ? `${idPrefix}-city-error`
                                    : undefined
                            "
                        />
                        <InputError
                            :id="`${idPrefix}-city-error`"
                            :message="form.errors.city"
                        />
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</template>
