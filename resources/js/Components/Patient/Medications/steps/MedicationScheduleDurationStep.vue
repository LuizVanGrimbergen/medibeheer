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

const { form, idPrefix } = defineProps<{
    form: MedicationCreateFormWithErrors;
    idPrefix: string;
}>();

const { t } = useI18n();
</script>

<template>
    <div class="space-y-8">
        <fieldset class="min-w-0 border-0 p-0">
            <legend
                :id="`${idPrefix}-schedule-duration-intake-period`"
                :class="cn(patientFormLabelClass, 'float-none w-full px-0 text-xl')"
            >
                {{ t('patient.medications.fields.intakePeriod') }}
            </legend>
            <div class="mt-6 space-y-8 md:mt-8">
                <div>
                    <Label
                        :for="`${idPrefix}-schedule-start-date`"
                        :class="cn(patientFormLabelClass, 'text-xl')"
                    >
                        {{ t('patient.medications.fields.startDate') }}
                    </Label>
                    <Input
                        :id="`${idPrefix}-schedule-start-date`"
                        v-model="form.schedule.start_date"
                        type="date"
                        name="schedule_start_date"
                        autocomplete="off"
                        :class="
                            cn(
                                patientFormFieldInputClass,
                                patientFormLargeTouchFieldClass,
                                form.errors['schedule.start_date']
                                    ? patientFormFieldInvalidClass
                                    : null,
                            )
                        "
                        :aria-invalid="Boolean(form.errors['schedule.start_date'])"
                        :aria-describedby="
                            form.errors['schedule.start_date']
                                ? `${idPrefix}-schedule-start-date-error`
                                : undefined
                        "
                    />
                    <InputError
                        :id="`${idPrefix}-schedule-start-date-error`"
                        :message="form.errors['schedule.start_date']"
                    />
                </div>

                <div>
                    <Label
                        :for="`${idPrefix}-schedule-end-date`"
                        :class="cn(patientFormLabelClass, 'text-xl')"
                    >
                        {{ t('patient.medications.fields.endDate') }}
                    </Label>
                    <Input
                        :id="`${idPrefix}-schedule-end-date`"
                        v-model="form.schedule.end_date"
                        type="date"
                        name="schedule_end_date"
                        autocomplete="off"
                        :class="
                            cn(
                                patientFormFieldInputClass,
                                patientFormLargeTouchFieldClass,
                                form.errors['schedule.end_date']
                                    ? patientFormFieldInvalidClass
                                    : null,
                            )
                        "
                        :aria-invalid="Boolean(form.errors['schedule.end_date'])"
                        :aria-describedby="
                            form.errors['schedule.end_date']
                                ? `${idPrefix}-schedule-end-date-error`
                                : undefined
                        "
                    />
                    <InputError
                        :id="`${idPrefix}-schedule-end-date-error`"
                        :message="form.errors['schedule.end_date']"
                    />
                </div>
            </div>
        </fieldset>
    </div>
</template>
