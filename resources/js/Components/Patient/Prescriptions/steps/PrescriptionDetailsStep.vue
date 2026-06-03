<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientFormCountPresetPicker from '@/Components/Patient/form/PatientFormCountPresetPicker.vue';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormSelectBaseClass,
    patientFormSelectChevronStyle,
} from '@/lib/patient/patientFormFieldClasses';
import type { PatientPrescriptionForm } from '@/lib/patient/prescriptions/patientPrescriptionFormTypes';
import type { PatientPrescriptionMedicationChoice } from '@/lib/patient/prescriptions/patientPrescriptionsScreenProps';
import type { MedicationTypeValue } from '@/lib/types';
import { cn } from '@/lib/utils';

const selectedMedicationId = defineModel<number | null>('selectedMedicationId', {
    required: true,
});

const props = defineProps<{
    form: PatientPrescriptionForm;
    idPrefix: string;
    medicationChoices: PatientPrescriptionMedicationChoice[];
    quantityClientError: string;
    medicationClientError: string;
}>();

const { t } = useI18n();

const quantityErrorMessage = computed((): string | undefined => {
    if (props.quantityClientError.length > 0) {
        return t(props.quantityClientError);
    }

    return props.form.errors.quantity;
});

const medicationErrorMessage = computed((): string | undefined => {
    if (props.medicationClientError.length > 0) {
        return t(props.medicationClientError);
    }

    return undefined;
});

const medicationSelectInvalid = computed(
    () => Boolean(medicationErrorMessage.value),
);

function medicationTypeLabel(type: string): string {
    return t(`patient.medications.types.${type as MedicationTypeValue}`);
}

function prescriptionQuantityOptionLabel(value: number): string {
    if (value === 1) {
        return t('patient.prescriptions.quantity.one');
    }

    return t('patient.prescriptions.quantity.nPrescriptions', {
        n: String(value),
    });
}
</script>

<template>
    <div class="space-y-6">
        <div class="min-w-0 space-y-2">
            <Label
                :for="`${idPrefix}-medication`"
                :class="patientFormLabelClass"
            >
                {{ t('patient.prescriptions.medicationLabel') }}
                <span class="text-danger"> *</span>
            </Label>
            <select
                :id="`${idPrefix}-medication`"
                v-model.number="selectedMedicationId"
                :class="
                    cn(
                        patientFormSelectBaseClass,
                        medicationSelectInvalid && patientFormFieldInvalidClass,
                    )
                "
                :style="patientFormSelectChevronStyle"
                required
            >
                <option
                    v-for="choice in medicationChoices"
                    :key="choice.id"
                    :value="choice.id"
                >
                    {{ choice.name }}
                    ({{ medicationTypeLabel(choice.type_medication) }})
                </option>
            </select>
            <InputError
                :id="`${idPrefix}-medication-error`"
                :message="medicationErrorMessage"
            />
        </div>

        <PatientFormCountPresetPicker
            :id-prefix="`${idPrefix}-quantity`"
            v-model="form.quantity"
            :label="t('patient.prescriptions.quantityLabel')"
            required
            :error-message="quantityErrorMessage"
            :option-label="prescriptionQuantityOptionLabel"
            :custom-trigger-label="t('patient.medications.intakePeriodPresets.custom')"
            :custom-placeholder="t('patient.prescriptions.quantity.customPlaceholder')"
            :custom-select-aria-label="
                t('patient.prescriptions.quantity.customSelectAriaLabel')
            "
        />
    </div>
</template>
