import type {
    MedicationCreateFormState,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import {
    tryMedicationWizardDetailsStep,
    tryMedicationWizardDoseTimesStep,
    tryMedicationWizardDurationStep,
    tryMedicationWizardNoteStockStep,
    tryMedicationWizardScheduleTimingStep,
    tryMedicationWizardTimesPerDayStep,
} from '@/lib/patient/medications/medicationWizardClientZod';

export type MedicationWizardStepAdvanceResult =
    | { ok: true; nextStep: MedicationFormWizardStep }
    | { ok: false; fieldErrors: Partial<Record<string, string>> };

export function attemptMedicationWizardAdvanceFromStep1(
    form: MedicationCreateFormState,
): MedicationWizardStepAdvanceResult {
    const check = tryMedicationWizardDetailsStep({
        name: form.name,
        dose: form.dose,
        dose_unit: form.dose_unit,
        type_medication: form.type_medication,
        strength: form.strength,
    });

    if (!check.ok) {
        return { ok: false, fieldErrors: check.fieldErrors };
    }

    return { ok: true, nextStep: 2 };
}

export function attemptMedicationWizardAdvanceFromStep2(
    form: MedicationCreateFormState,
): MedicationWizardStepAdvanceResult {
    const check = tryMedicationWizardScheduleTimingStep(form.schedule);

    if (!check.ok) {
        return { ok: false, fieldErrors: check.fieldErrors };
    }

    return { ok: true, nextStep: 3 };
}

export function attemptMedicationWizardAdvanceFromStep3(
    form: MedicationCreateFormState,
): MedicationWizardStepAdvanceResult {
    const check = tryMedicationWizardTimesPerDayStep(form.schedule);

    if (!check.ok) {
        return { ok: false, fieldErrors: check.fieldErrors };
    }

    return { ok: true, nextStep: 4 };
}

export function attemptMedicationWizardAdvanceFromStep4(
    form: MedicationCreateFormState,
): MedicationWizardStepAdvanceResult {
    const check = tryMedicationWizardDoseTimesStep(form.schedule);

    if (!check.ok) {
        return { ok: false, fieldErrors: check.fieldErrors };
    }

    return { ok: true, nextStep: 5 };
}

export function attemptMedicationWizardAdvanceFromStep5(
    form: MedicationCreateFormState,
): MedicationWizardStepAdvanceResult {
    const check = tryMedicationWizardDurationStep(form.schedule);

    if (!check.ok) {
        return { ok: false, fieldErrors: check.fieldErrors };
    }

    return { ok: true, nextStep: 6 };
}

export function attemptMedicationWizardAdvanceFromStep6(
    form: MedicationCreateFormState,
): MedicationWizardStepAdvanceResult {
    const check = tryMedicationWizardNoteStockStep({
        note: form.note,
        current_stock: form.current_stock,
        low_stock: form.low_stock,
    });

    if (!check.ok) {
        return { ok: false, fieldErrors: check.fieldErrors };
    }

    return { ok: true, nextStep: 7 };
}
