<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { useI18n } from 'vue-i18n';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    patientFormFieldInputClass,
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormLargeTouchFieldClass,
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
            <div>
                <Label
                    :for="`${props.idPrefix}-current-stock`"
                    :class="cn(patientFormLabelClass, 'text-xl')"
                >
                    {{ t('patient.medications.fields.currentStock') }}
                </Label>
                <Input
                    :id="`${props.idPrefix}-current-stock`"
                    v-model="props.form.current_stock"
                    type="text"
                    name="current_stock"
                    autocomplete="off"
                    maxlength="500"
                    :placeholder="t('patient.medications.fields.currentStockPlaceholder')"
                    :class="
                        cn(
                            patientFormFieldInputClass,
                            patientFormLargeTouchFieldClass,
                            'mt-2',
                            props.form.errors.current_stock ? patientFormFieldInvalidClass : null,
                        )
                    "
                    :aria-invalid="Boolean(props.form.errors.current_stock)"
                    :aria-describedby="
                        props.form.errors.current_stock
                            ? `${props.idPrefix}-current-stock-error`
                            : undefined
                    "
                />
                <InputError
                    :id="`${props.idPrefix}-current-stock-error`"
                    :message="props.form.errors.current_stock"
                />
            </div>
            <div>
                <Label
                    :for="`${props.idPrefix}-low-stock`"
                    :class="cn(patientFormLabelClass, 'text-xl')"
                >
                    {{ t('patient.medications.fields.lowStock') }}
                </Label>
                <Input
                    :id="`${props.idPrefix}-low-stock`"
                    v-model="props.form.low_stock"
                    type="text"
                    name="low_stock"
                    autocomplete="off"
                    maxlength="64"
                    :placeholder="t('patient.medications.fields.lowStockPlaceholder')"
                    :class="
                        cn(
                            patientFormFieldInputClass,
                            patientFormLargeTouchFieldClass,
                            'mt-2',
                            props.form.errors.low_stock ? patientFormFieldInvalidClass : null,
                        )
                    "
                    :aria-invalid="Boolean(props.form.errors.low_stock)"
                    :aria-describedby="
                        props.form.errors.low_stock
                            ? `${props.idPrefix}-low-stock-error`
                            : undefined
                    "
                />
                <InputError
                    :id="`${props.idPrefix}-low-stock-error`"
                    :message="props.form.errors.low_stock"
                />
            </div>
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
