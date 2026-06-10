<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { useI18n } from 'vue-i18n';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/form/AppointmentFormTypes';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    mobileShellFormFieldInputClass,
    mobileShellFormFieldInvalidClass,
    mobileShellFormLabelClass,
} from '@/lib/shell/mobileShellFormFieldClasses';
import { cn } from '@/lib/utils';

const {
    form,
    idPrefix,
    required = true,
} = defineProps<{
    form: AppointmentFormWithErrors;
    idPrefix: string;
    required?: boolean;
}>();

const { t } = useI18n();
</script>

<template>
    <div class="space-y-5">
        <div>
            <Label :for="`${idPrefix}-street`" :class="mobileShellFormLabelClass">
                {{ t('patient.appointments.fields.street') }}
                <span v-if="required" class="text-danger">*</span>
            </Label>
            <Input
                :id="`${idPrefix}-street`"
                v-model="form.street"
                type="text"
                :aria-required="required"
                autocomplete="address-line1"
                :class="
                    cn(
                        mobileShellFormFieldInputClass,
                        form.errors.street
                            ? mobileShellFormFieldInvalidClass
                            : null,
                    )
                "
                :placeholder="
                    t('patient.appointments.fields.streetPlaceholder')
                "
                :aria-invalid="Boolean(form.errors.street)"
                :aria-describedby="
                    form.errors.street ? `${idPrefix}-street-error` : undefined
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
                :class="mobileShellFormLabelClass"
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
                        mobileShellFormFieldInputClass,
                        form.errors.house_number
                            ? mobileShellFormFieldInvalidClass
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
                    :class="mobileShellFormLabelClass"
                >
                    {{ t('patient.appointments.fields.postalCode') }}
                    <span v-if="required" class="text-danger">*</span>
                </Label>
                <Input
                    :id="`${idPrefix}-postal-code`"
                    v-model="form.postal_code"
                    type="text"
                    inputmode="numeric"
                    :aria-required="required"
                    maxlength="4"
                    autocomplete="postal-code"
                    :class="
                        cn(
                            mobileShellFormFieldInputClass,
                            form.errors.postal_code
                                ? mobileShellFormFieldInvalidClass
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
                <Label :for="`${idPrefix}-city`" :class="mobileShellFormLabelClass">
                    {{ t('patient.appointments.fields.city') }}
                    <span v-if="required" class="text-danger">*</span>
                </Label>
                <Input
                    :id="`${idPrefix}-city`"
                    v-model="form.city"
                    type="text"
                    :aria-required="required"
                    autocomplete="address-level2"
                    :class="
                        cn(
                            mobileShellFormFieldInputClass,
                            form.errors.city
                                ? mobileShellFormFieldInvalidClass
                                : null,
                        )
                    "
                    :placeholder="
                        t('patient.appointments.fields.cityPlaceholder')
                    "
                    :aria-invalid="Boolean(form.errors.city)"
                    :aria-describedby="
                        form.errors.city ? `${idPrefix}-city-error` : undefined
                    "
                />
                <InputError
                    :id="`${idPrefix}-city-error`"
                    :message="form.errors.city"
                />
            </div>
        </div>
    </div>
</template>
