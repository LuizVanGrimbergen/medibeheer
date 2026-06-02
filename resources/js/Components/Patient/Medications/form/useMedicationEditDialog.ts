import { nextTick, ref, watch } from 'vue';
import type {
    MedicationCreateFormWithErrors,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { applyMedicationWizardFieldErrorsToForm } from '@/lib/patient/medications/form-wizard/medicationFormWizardApplyFieldErrors';
import { scrollMedicationFormFirstFieldErrorIntoView } from '@/lib/patient/medications/form-wizard/medicationFormWizardDom';
import { medicationWizardStepForInertiaFormErrors } from '@/lib/patient/medications/form-wizard/medicationFormWizardInertiaErrors';
import { evaluateMedicationWizardSubmitClientValidation } from '@/lib/patient/medications/medicationWizardClientZod';

type MedicationEditDialogStep = 0 | 1 | 2 | 3 | 4 | 5 | 6;

function medicationWizardStepToEditDialogStep(
    step: MedicationFormWizardStep,
): MedicationEditDialogStep {
    if (step === 7) {
        return 0;
    }

    return step;
}

export type UseMedicationEditDialogOptions = {
    open: () => boolean;
    form: () => MedicationCreateFormWithErrors;
    idPrefix: () => string;
    onSubmit: () => void;
};

export function useMedicationEditDialog(options: UseMedicationEditDialogOptions) {
    const editingStep = ref<MedicationEditDialogStep>(0);

    watch(
        () => options.open(),
        (isOpen) => {
            if (isOpen) {
                editingStep.value = 0;
            }
        },
    );

    watch(
        () => options.form().errors,
        (errors) => {
            if (!options.open()) {
                return;
            }

            const step = medicationWizardStepForInertiaFormErrors(errors);

            if (step !== null) {
                editingStep.value = medicationWizardStepToEditDialogStep(step);
            }
        },
        { deep: true },
    );

    function medicationEditSummaryGoToField(
        step: MedicationFormWizardStep,
        focusElementIdSuffix?: string,
    ): void {
        if (options.form().processing) {
            return;
        }

        if (step < 1 || step > 6) {
            return;
        }

        editingStep.value = step as MedicationEditDialogStep;

        void nextTick(() => {
            const suffix =
                focusElementIdSuffix !== undefined && focusElementIdSuffix.length > 0
                    ? focusElementIdSuffix
                    : 'name';

            const el = document.getElementById(`${options.idPrefix()}-${suffix}`);

            if (el === null) {
                return;
            }

            el.focus({ preventScroll: true });
            el.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        });
    }

    function handleSubmit(): void {
        const form = options.form();

        if (form.processing || editingStep.value < 1) {
            return;
        }

        const result = evaluateMedicationWizardSubmitClientValidation(form);

        if (!result.ok) {
            form.clearErrors();
            applyMedicationWizardFieldErrorsToForm(form, result.mergedFieldErrors);
            scrollMedicationFormFirstFieldErrorIntoView(
                options.idPrefix(),
                result.mergedFieldErrors,
            );
            editingStep.value = medicationWizardStepToEditDialogStep(result.step);

            return;
        }

        form.clearErrors();
        options.onSubmit();
    }

    return {
        editingStep,
        handleSubmit,
        medicationEditSummaryGoToField,
    };
}
