import type { Ref } from 'vue';
import { nextTick } from 'vue';
import type {
    MedicationCreateFormWithErrors,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import {
    focusMedicationWizardFirstDoseTimeField,
    focusMedicationWizardMealTimingField,
    focusMedicationWizardNameField,
    focusMedicationWizardScheduleStartDateField,
    focusMedicationWizardStockOrNoteField,
    focusMedicationWizardTimesPerDayField,
    tryFocusMedicationWizardElementBySuffix,
} from './medicationFormWizardFocus';

export function runMedicationWizardGoToStepFromSummary(params: {
    open: () => boolean;
    form: () => MedicationCreateFormWithErrors;
    idPrefix: () => string;
    currentStep: Ref<MedicationFormWizardStep>;
    step: MedicationFormWizardStep;
    focusElementIdSuffix?: string;
}): void {
    const { open, form, idPrefix, currentStep, step, focusElementIdSuffix } =
        params;
    const inertiaForm = form();

    if (!open() || inertiaForm.processing || currentStep.value !== 7) {
        return;
    }

    inertiaForm.clearErrors();
    currentStep.value = step;

    void nextTick(() => {
        const prefix = idPrefix();

        if (
            focusElementIdSuffix !== undefined &&
            focusElementIdSuffix.length > 0 &&
            tryFocusMedicationWizardElementBySuffix(
                prefix,
                focusElementIdSuffix,
            )
        ) {
            return;
        }

        if (step === 1) {
            focusMedicationWizardNameField(prefix);

            return;
        }

        if (step === 2) {
            focusMedicationWizardMealTimingField(
                prefix,
                inertiaForm.schedule.meal_timing,
            );

            return;
        }

        if (step === 3) {
            focusMedicationWizardTimesPerDayField(
                prefix,
                inertiaForm.schedule.times_per_day.trim(),
            );

            return;
        }

        if (step === 4) {
            focusMedicationWizardFirstDoseTimeField(prefix);

            return;
        }

        if (step === 5) {
            focusMedicationWizardScheduleStartDateField(prefix);

            return;
        }

        if (step === 6) {
            focusMedicationWizardStockOrNoteField(prefix);
        }
    });
}
