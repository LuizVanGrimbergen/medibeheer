<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    mobileShellFormFieldInvalidClass,
    mobileShellFormLabelClass,
    mobileShellFormNativeDateTimeInputClass,
} from '@/lib/shell/mobileShellFormFieldClasses';
import type { PatientPrescriptionForm } from '@/lib/patient/prescriptions/patientPrescriptionFormTypes';
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

const prescriptionQuantity = computed(() => props.form.quantity ?? 0);

function isLastPrescriptionField(index: number): boolean {
    return index === prescriptionQuantity.value;
}

function expiryDateFieldLabel(index: number): string {
    if (isLastPrescriptionField(index) && prescriptionQuantity.value > 1) {
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
            v-for="index in prescriptionQuantity"
            :key="index"
            class="min-w-0 space-y-2"
        >
            <Label
                :for="`${idPrefix}-expiry-${index - 1}`"
                :class="mobileShellFormLabelClass"
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
                        mobileShellFormNativeDateTimeInputClass,
                        (expiryDatesErrorMessage ||
                            expiryDateFieldError(index - 1)) &&
                            mobileShellFormFieldInvalidClass,
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
