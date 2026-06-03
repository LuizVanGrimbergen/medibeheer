<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import MedicationStockBoxCalculator from '@/Components/Patient/Medications/form/MedicationStockBoxCalculator.vue';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { medicationStockDisplayDoseUnit } from '@/lib/patient/medications/stock/medicationStockDisplayDoseUnit';
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

const stockDisplayDoseUnit = computed(() =>
    medicationStockDisplayDoseUnit(props.form.dose_unit, props.form.strength_unit),
);
</script>

<template>
    <div class="space-y-3 md:space-y-4">
        <div
            v-if="props.showStockFields"
            class="space-y-8"
        >
            <fieldset>
                <legend :class="cn(patientFormLabelClass, 'text-xl')">
                    {{ t('patient.medications.fields.currentStock') }} <span class="text-danger">*</span>
                </legend>
                <MedicationStockBoxCalculator
                    v-model="props.form.current_stock"
                    v-model:pieces-per-package="props.form.stock_pieces_per_package"
                    :id-prefix="props.idPrefix"
                    :medication-type="props.form.type_medication"
                    :dose-unit="stockDisplayDoseUnit ?? ''"
                    :error-message="
                        props.form.errors.current_stock ||
                        props.form.errors.stock_pieces_per_package
                    "
                    class="mt-3"
                />
            </fieldset>
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
