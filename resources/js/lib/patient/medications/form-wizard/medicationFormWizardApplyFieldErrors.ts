import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { looseMedicationWizardInertiaForm } from './medicationFormWizardLooseInertiaForm';

export function applyMedicationWizardFieldErrorsToForm(
    form: MedicationCreateFormWithErrors,
    fieldErrors: Partial<Record<string, string>>,
): void {
    const target = looseMedicationWizardInertiaForm(form);

    for (const [rawKey, message] of Object.entries(fieldErrors)) {
        if (message === undefined || message.length < 1) {
            continue;
        }

        target.setError(rawKey, message);
    }
}
