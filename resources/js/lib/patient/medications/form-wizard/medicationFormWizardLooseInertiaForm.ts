import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';

type LooseInertiaForm = {
    setError: (key: string, value: string) => void;
    clearErrors: (...keys: string[]) => void;
};

export function looseMedicationWizardInertiaForm(form: MedicationCreateFormWithErrors): LooseInertiaForm {
    return form as unknown as LooseInertiaForm;
}
