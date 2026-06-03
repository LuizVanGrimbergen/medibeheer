<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { todayLocalIsoDate } from '@/lib/patient/medications/schedule/medicationScheduleDuration';
import type {
    MedicationScheduleDurationPresetKey,
    MedicationScheduleDurationTimedPresetKey,
} from '@/lib/patient/medications/schedule/medicationScheduleDurationPresets';
import {
    applyMedicationScheduleDurationPreset,
    applyMedicationScheduleOngoingPreset,
    detectMedicationScheduleDurationPreset,
    MEDICATION_SCHEDULE_DURATION_ONGOING_KEY,
    MEDICATION_SCHEDULE_DURATION_UI_PRESET_KEYS,
} from '@/lib/patient/medications/schedule/medicationScheduleDurationPresets';
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

const prefersCustomDuration = ref(
    detectMedicationScheduleDurationPreset(
        form.schedule.start_date,
        form.schedule.end_date,
    ) === 'custom',
);

const activeDurationChoice = computed(
    (): ReturnType<typeof detectMedicationScheduleDurationPreset> => {
        if (prefersCustomDuration.value) {
            return 'custom';
        }

        return detectMedicationScheduleDurationPreset(
            form.schedule.start_date,
            form.schedule.end_date,
        );
    },
);

const showCustomDateFields = computed(
    () => activeDurationChoice.value === 'custom',
);

const durationPresetLabel = (
    preset: MedicationScheduleDurationPresetKey,
): string => t(`patient.medications.intakePeriodPresets.${preset}`);

const selectTimedPreset = (
    preset: MedicationScheduleDurationTimedPresetKey,
): void => {
    prefersCustomDuration.value = false;
    applyMedicationScheduleDurationPreset(form.schedule, preset);
};

const selectOngoing = (): void => {
    prefersCustomDuration.value = false;
    applyMedicationScheduleOngoingPreset(form.schedule);
};

const selectCustom = (): void => {
    prefersCustomDuration.value = true;

    if (form.schedule.start_date.trim().length < 1) {
        form.schedule.start_date = todayLocalIsoDate();
    }
};

const selectPreset = (preset: MedicationScheduleDurationPresetKey): void => {
    if (preset === MEDICATION_SCHEDULE_DURATION_ONGOING_KEY) {
        selectOngoing();

        return;
    }

    selectTimedPreset(preset);
};

watch(
    () => [form.schedule.start_date, form.schedule.end_date] as const,
    () => {
        const detected = detectMedicationScheduleDurationPreset(
            form.schedule.start_date,
            form.schedule.end_date,
        );

        if (detected === null) {
            prefersCustomDuration.value = false;

            return;
        }

        if (prefersCustomDuration.value) {
            return;
        }

        if (detected === 'custom') {
            prefersCustomDuration.value = true;
        }
    },
);
</script>

<template>
    <div class="space-y-8">
        <fieldset class="min-w-0 border-0 p-0">
            <legend
                :id="`${idPrefix}-schedule-duration-intake-period`"
                :class="
                    cn(patientFormLabelClass, 'float-none w-full px-0 text-xl')
                "
            >
                {{ t('patient.medications.fields.intakePeriod') }}
                <span class="text-danger">*</span>
            </legend>
            <div
                :class="
                    cn(
                        'mt-2 touch-manipulation space-y-4',
                        form.errors['schedule.start_date'] ||
                            form.errors['schedule.end_date']
                            ? 'ring-danger/25 rounded-2xl p-0.5 ring-2'
                            : null,
                    )
                "
            >
                <div
                    class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3"
                    role="radiogroup"
                    :aria-labelledby="`${idPrefix}-schedule-duration-intake-period`"
                    :aria-invalid="
                        Boolean(
                            form.errors['schedule.start_date'] ||
                            form.errors['schedule.end_date'],
                        )
                    "
                    :aria-describedby="
                        form.errors['schedule.start_date'] ||
                        form.errors['schedule.end_date']
                            ? `${idPrefix}-schedule-duration-error`
                            : undefined
                    "
                >
                    <button
                        v-for="preset in MEDICATION_SCHEDULE_DURATION_UI_PRESET_KEYS"
                        :id="`${idPrefix}-schedule-duration-preset-${preset}`"
                        :key="preset"
                        type="button"
                        class="focus-visible:border-focus focus-visible:ring-focus/30 min-h-14 rounded-2xl border-2 px-4 py-3.5 text-left text-base leading-snug font-semibold transition-colors focus-visible:ring-2 focus-visible:outline-none md:min-h-[3.75rem] md:px-5 md:text-lg"
                        :class="
                            activeDurationChoice === preset
                                ? 'border-primary bg-primary/10 text-text-heading'
                                : 'border-border bg-surface text-text hover:bg-surface-hover'
                        "
                        :aria-pressed="activeDurationChoice === preset"
                        @click="selectPreset(preset)"
                    >
                        {{ durationPresetLabel(preset) }}
                    </button>
                    <button
                        :id="`${idPrefix}-schedule-duration-custom`"
                        type="button"
                        class="focus-visible:border-focus focus-visible:ring-focus/30 min-h-14 rounded-2xl border-2 px-4 py-3.5 text-left text-base leading-snug font-semibold transition-colors focus-visible:ring-2 focus-visible:outline-none md:min-h-[3.75rem] md:px-5 md:text-lg"
                        :class="
                            activeDurationChoice === 'custom'
                                ? 'border-primary bg-primary/10 text-text-heading'
                                : 'border-border bg-surface text-text hover:bg-surface-hover'
                        "
                        :aria-pressed="activeDurationChoice === 'custom'"
                        @click="selectCustom"
                    >
                        {{
                            t('patient.medications.intakePeriodPresets.custom')
                        }}
                    </button>
                </div>
                <div v-if="showCustomDateFields" class="mt-6 space-y-8 md:mt-8">
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
                            :aria-invalid="
                                Boolean(form.errors['schedule.start_date'])
                            "
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
                            :aria-invalid="
                                Boolean(form.errors['schedule.end_date'])
                            "
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
            </div>
            <InputError
                v-if="
                    !showCustomDateFields &&
                    (form.errors['schedule.start_date'] ||
                        form.errors['schedule.end_date'])
                "
                :id="`${idPrefix}-schedule-duration-error`"
                :message="
                    form.errors['schedule.start_date'] ??
                    form.errors['schedule.end_date']
                "
            />
        </fieldset>
    </div>
</template>
