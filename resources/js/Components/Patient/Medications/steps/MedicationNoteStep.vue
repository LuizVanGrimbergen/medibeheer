<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { useI18n } from 'vue-i18n';
import MedicationStockAmountField from '@/Components/Patient/Medications/form/MedicationStockAmountField.vue';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    patientFormFieldInputClass,
    patientFormFieldInvalidClass,
    patientFormLabelClass,
} from '@/lib/patient/patientFormFieldClasses';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        form: MedicationCreateFormWithErrors;
        idPrefix: string;
        showStockFields?: boolean;
    }>(),
    {
        showStockFields: false,
    },
);

const { t } = useI18n();
</script>

<template>
    <div class="space-y-3 md:space-y-4">
        <div
            v-if="props.showStockFields"
            class="space-y-8"
        >
            <MedicationStockAmountField
                v-model="props.form.current_stock"
                :id-prefix="props.idPrefix"
                field-id-suffix="current-stock"
                label-key="patient.medications.fields.currentStock"
                placeholder-example-amount="200"
                fallback-placeholder-key="patient.medications.fields.currentStockPlaceholder"
                :dose-unit="props.form.dose_unit"
                :maxlength="500"
                :error-message="props.form.errors.current_stock"
            />
            <MedicationStockAmountField
                v-model="props.form.low_stock"
                :id-prefix="props.idPrefix"
                field-id-suffix="low-stock"
                label-key="patient.medications.fields.lowStock"
                placeholder-example-amount="40"
                fallback-placeholder-key="patient.medications.fields.lowStockPlaceholder"
                :dose-unit="props.form.dose_unit"
                :maxlength="64"
                :error-message="props.form.errors.low_stock"
            />
        </div>
        <div>
            <Label
                :for="`${props.idPrefix}-note`"
                :class="cn(patientFormLabelClass, 'text-xl')"
            >
                {{ t('patient.medications.fields.noteFormLabel') }}
            </Label>
            <textarea
                :id="`${props.idPrefix}-note`"
                v-model="props.form.note"
                name="note"
                rows="6"
                maxlength="2000"
                autocomplete="off"
                :placeholder="t('patient.medications.fields.notePlaceholder')"
                :class="
                    cn(
                        patientFormFieldInputClass,
                        'mt-2 min-h-42 w-full resize-y py-4 text-lg leading-relaxed md:min-h-48 md:text-xl',
                        props.form.errors.note ? patientFormFieldInvalidClass : null,
                    )
                "
                :aria-invalid="Boolean(props.form.errors.note)"
                :aria-describedby="
                    props.form.errors.note ? `${props.idPrefix}-note-error` : undefined
                "
            />
            <InputError
                :id="`${props.idPrefix}-note-error`"
                :message="props.form.errors.note"
            />
        </div>
    </div>
</template>
