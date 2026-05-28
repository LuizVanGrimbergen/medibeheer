import { computed, nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type {
    MedicationCreateFormWithErrors,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { applyMedicationWizardFieldErrorsToForm } from '@/lib/patient/medications/form-wizard/medicationFormWizardApplyFieldErrors';
import {
    attemptMedicationWizardAdvanceFromStep1,
    attemptMedicationWizardAdvanceFromStep2,
    attemptMedicationWizardAdvanceFromStep3,
    attemptMedicationWizardAdvanceFromStep4,
    attemptMedicationWizardAdvanceFromStep5,
    attemptMedicationWizardAdvanceFromStep6,
} from '@/lib/patient/medications/form-wizard/medicationFormWizardAttemptStepAdvances';
import { MEDICATION_FORM_WIZARD_STEP_TOTAL } from '@/lib/patient/medications/form-wizard/medicationFormWizardConstants';
import { scrollMedicationFormFirstFieldErrorIntoView } from '@/lib/patient/medications/form-wizard/medicationFormWizardDom';
import {
    focusMedicationWizardFirstDoseTimeField,
    focusMedicationWizardMealTimingField,
    focusMedicationWizardNameField,
    focusMedicationWizardScheduleStartDateField,
    focusMedicationWizardStockOrNoteField,
    focusMedicationWizardSummaryTitleField,
    focusMedicationWizardTimesPerDayField,
} from '@/lib/patient/medications/form-wizard/medicationFormWizardFocus';
import { handleMedicationWizardFooterBack } from '@/lib/patient/medications/form-wizard/medicationFormWizardFooterBack';
import { runMedicationWizardGoToStepFromSummary } from '@/lib/patient/medications/form-wizard/medicationFormWizardGoToStepFromSummary';
import { medicationWizardStepForInertiaFormErrors } from '@/lib/patient/medications/form-wizard/medicationFormWizardInertiaErrors';
import { evaluateMedicationWizardSubmitClientValidation } from '@/lib/patient/medications/medicationWizardClientZod';

export type UseMedicationFormWizardOptions = {
    open: () => boolean;
    form: () => MedicationCreateFormWithErrors;
    idPrefix: () => string;
    onSubmit: () => void;
    initialStep?: () => MedicationFormWizardStep;
};

function rejectWizardAdvanceWithFieldErrors(
    form: MedicationCreateFormWithErrors,
    idPrefix: string,
    fieldErrors: Partial<Record<string, string>>,
): void {
    form.clearErrors();
    applyMedicationWizardFieldErrorsToForm(form, fieldErrors);
    scrollMedicationFormFirstFieldErrorIntoView(idPrefix, fieldErrors);
}

export function useMedicationFormWizard(options: UseMedicationFormWizardOptions) {
    const { t } = useI18n();

    const currentStep = ref<MedicationFormWizardStep>(1);

    const medicationProgressLabel = computed(() =>
        t('patient.medications.stepsProgress', {
            current: currentStep.value,
            total: MEDICATION_FORM_WIZARD_STEP_TOTAL,
        }),
    );

    function handleMedicationFormFooterBack(): void {
        handleMedicationWizardFooterBack({
            form: options.form,
            currentStep,
        });
    }

    function tryAdvanceToScheduleStep(): void {
        const form = options.form();
        const result = attemptMedicationWizardAdvanceFromStep1(form);

        if (!result.ok) {
            rejectWizardAdvanceWithFieldErrors(form, options.idPrefix(), result.fieldErrors);

            return;
        }

        form.clearErrors();
        currentStep.value = result.nextStep;

        void nextTick(() => {
            focusMedicationWizardMealTimingField(options.idPrefix(), form.schedule.meal_timing);
        });
    }

    function tryAdvanceFromStepTwoToTimesPerDay(): void {
        const form = options.form();
        const result = attemptMedicationWizardAdvanceFromStep2(form);

        if (!result.ok) {
            rejectWizardAdvanceWithFieldErrors(form, options.idPrefix(), result.fieldErrors);

            return;
        }

        form.clearErrors();
        currentStep.value = result.nextStep;

        void nextTick(() => {
            focusMedicationWizardTimesPerDayField(
                options.idPrefix(),
                form.schedule.times_per_day.trim(),
            );
        });
    }

    function tryAdvanceFromStepThreeToDoseTimes(): void {
        const form = options.form();
        const result = attemptMedicationWizardAdvanceFromStep3(form);

        if (!result.ok) {
            rejectWizardAdvanceWithFieldErrors(form, options.idPrefix(), result.fieldErrors);

            return;
        }

        form.clearErrors();
        currentStep.value = result.nextStep;

        void nextTick(() => {
            focusMedicationWizardFirstDoseTimeField(options.idPrefix());
        });
    }

    function tryAdvanceFromStepFourToDuration(): void {
        const form = options.form();
        const result = attemptMedicationWizardAdvanceFromStep4(form);

        if (!result.ok) {
            rejectWizardAdvanceWithFieldErrors(form, options.idPrefix(), result.fieldErrors);

            return;
        }

        form.clearErrors();
        currentStep.value = result.nextStep;

        void nextTick(() => {
            focusMedicationWizardScheduleStartDateField(options.idPrefix());
        });
    }

    function tryAdvanceFromStepFiveToNote(): void {
        const form = options.form();
        const result = attemptMedicationWizardAdvanceFromStep5(form);

        if (!result.ok) {
            rejectWizardAdvanceWithFieldErrors(form, options.idPrefix(), result.fieldErrors);

            return;
        }

        form.clearErrors();
        currentStep.value = result.nextStep;

        void nextTick(() => {
            focusMedicationWizardStockOrNoteField(options.idPrefix());
        });
    }

    function tryAdvanceFromStepSixToSummary(): void {
        const form = options.form();
        const result = attemptMedicationWizardAdvanceFromStep6(form);

        if (!result.ok) {
            rejectWizardAdvanceWithFieldErrors(form, options.idPrefix(), result.fieldErrors);

            return;
        }

        form.clearErrors();
        currentStep.value = result.nextStep;

        void nextTick(() => {
            focusMedicationWizardSummaryTitleField(options.idPrefix());
        });
    }

    function trySubmitMedicationWithSchedule(): void {
        const form = options.form();
        const result = evaluateMedicationWizardSubmitClientValidation(form);

        if (!result.ok) {
            rejectWizardAdvanceWithFieldErrors(form, options.idPrefix(), result.mergedFieldErrors);
            currentStep.value = result.step;

            return;
        }

        form.clearErrors();
        options.onSubmit();
    }

    function handleSubmit(): void {
        if (options.form().processing) {
            return;
        }

        if (currentStep.value === 1) {
            tryAdvanceToScheduleStep();

            return;
        }

        if (currentStep.value === 2) {
            tryAdvanceFromStepTwoToTimesPerDay();

            return;
        }

        if (currentStep.value === 3) {
            tryAdvanceFromStepThreeToDoseTimes();

            return;
        }

        if (currentStep.value === 4) {
            tryAdvanceFromStepFourToDuration();

            return;
        }

        if (currentStep.value === 5) {
            tryAdvanceFromStepFiveToNote();

            return;
        }

        if (currentStep.value === 6) {
            tryAdvanceFromStepSixToSummary();

            return;
        }

        if (currentStep.value === 7) {
            trySubmitMedicationWithSchedule();
        }
    }

    function focusWizardStepField(step: MedicationFormWizardStep): void {
        const idPrefix = options.idPrefix();

        if (step === 7) {
            focusMedicationWizardSummaryTitleField(idPrefix);

            return;
        }

        if (step === 2) {
            focusMedicationWizardMealTimingField(
                idPrefix,
                options.form().schedule.meal_timing,
            );

            return;
        }

        focusMedicationWizardNameField(idPrefix);
    }

    function resetWizardToInitialStep(): void {
        currentStep.value = options.initialStep?.() ?? 1;

        void nextTick(() => {
            focusWizardStepField(currentStep.value);
        });
    }

    watch(
        () => options.open(),
        (isOpen) => {
            if (!isOpen) {
                currentStep.value = 1;

                return;
            }

            resetWizardToInitialStep();
        },
        { flush: 'post' },
    );

    watch(
        () => options.form().errors,
        (errors) => {
            if (!options.open()) {
                return;
            }

            const step = medicationWizardStepForInertiaFormErrors(errors);

            if (step !== null) {
                currentStep.value = step;
            }
        },
        { deep: true },
    );

    function goToMedicationWizardStepFromSummary(
        step: MedicationFormWizardStep,
        focusElementIdSuffix?: string,
    ): void {
        runMedicationWizardGoToStepFromSummary({
            open: options.open,
            form: options.form,
            idPrefix: options.idPrefix,
            currentStep,
            step,
            focusElementIdSuffix,
        });
    }

    return {
        currentStep,
        medicationProgressLabel,
        handleSubmit,
        handleMedicationFormFooterBack,
        goToMedicationWizardStepFromSummary,
        resetWizardToInitialStep,
    };
}
