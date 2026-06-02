<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import type { PatientPrescriptionForm } from '@/lib/patient/prescriptions/patientPrescriptionFormTypes';
import {
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormNativeDateTimeInputClass,
} from '@/lib/patient/patientFormFieldClasses';
import { cn } from '@/lib/utils';

const props = defineProps<{
    form: PatientPrescriptionForm;
    idPrefix: string;
    expiryDatesClientError: string;
}>();

const { t } = useI18n();

const expiryDatesErrorMessage = computed((): string | undefined => {
    if (props.expiryDatesClientError.length > 0) {
        return t(props.expiryDatesClientError);
    }

    return props.form.errors.prescription_expiry_dates;
});

function expiryDateFieldError(index: number): string | undefined {
    return props.form.errors[`prescription_expiry_dates.${index}`];
}

function isLastPrescriptionField(index: number): boolean {
    return index === props.form.quantity;
}

function expiryDateFieldLabel(index: number): string {
    if (isLastPrescriptionField(index) && props.form.quantity > 1) {
        return t('patient.prescriptions.expiryDateLastPrescriptionLabel', {
            number: String(index),
        });
    }

    return t('patient.prescriptions.expiryDateLabel', {
        number: String(index),
    });
}
</script>

<template>
    <div class="space-y-5">
        <div
            v-for="index in form.quantity"
            :key="index"
            class="min-w-0 space-y-2"
        >
            <Label
                :for="`${idPrefix}-expiry-${index - 1}`"
                :class="patientFormLabelClass"
            >
                {{ expiryDateFieldLabel(index) }}
                <span class="text-danger"> *</span>
            </Label>

            <input
                :id="`${idPrefix}-expiry-${index - 1}`"
                v-model="form.prescription_expiry_dates[index - 1]"
                type="date"
                required
                :class="
                    cn(
                        patientFormNativeDateTimeInputClass,
                        (expiryDatesErrorMessage ||
                            expiryDateFieldError(index - 1)) &&
                            patientFormFieldInvalidClass,
                    )
                "
            />

            <InputError
                :id="`${idPrefix}-expiry-${index - 1}-error`"
                :message="expiryDateFieldError(index - 1)"
            />
        </div>

        <InputError
            :id="`${idPrefix}-expiry-dates-error`"
            :message="expiryDatesErrorMessage"
        />
    </div>
</template>
