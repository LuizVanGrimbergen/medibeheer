import type { Ref } from 'vue';
import type {
    MedicationCreateFormWithErrors,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { looseMedicationWizardInertiaForm } from './medicationFormWizardLooseInertiaForm';

export function handleMedicationWizardFooterBack(params: {
    form: () => MedicationCreateFormWithErrors;
    currentStep: Ref<MedicationFormWizardStep>;
}): void {
    const { form, currentStep } = params;

    if (form().processing) {
        return;
    }

    const inertiaForm = looseMedicationWizardInertiaForm(form());

    if (currentStep.value === 7) {
        currentStep.value = 6;

        return;
    }

    if (currentStep.value === 6) {
        inertiaForm.clearErrors('note');
        currentStep.value = 5;

        return;
    }

    if (currentStep.value === 5) {
        inertiaForm.clearErrors('schedule.start_date', 'schedule.end_date');
        currentStep.value = 4;

        return;
    }

    if (currentStep.value === 4) {
        inertiaForm.clearErrors('schedule.dose_time');
        currentStep.value = 3;

        return;
    }

    if (currentStep.value === 3) {
        inertiaForm.clearErrors('schedule.times_per_day', 'schedule.dose_time');
        currentStep.value = 2;

        return;
    }

    if (currentStep.value === 2) {
        inertiaForm.clearErrors(
            'schedule.meal_timing',
            'schedule.intake_frequency',
            'schedule.intake_weekdays',
            'schedule.times_per_day',
            'schedule.dose_time',
            'schedule.start_date',
            'schedule.end_date',
        );
        currentStep.value = 1;
    }
}
