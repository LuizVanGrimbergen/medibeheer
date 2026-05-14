import type { MedicationFormWizardStep } from '@/Components/Patient/Medications/form/MedicationFormTypes';

export type MedicationWizardClientParseResult =
    | { ok: true }
    | { ok: false; fieldErrors: Partial<Record<string, string>> };

export type MedicationWizardSubmitClientValidationResult =
    | { ok: true }
    | {
          ok: false;
          mergedFieldErrors: Partial<Record<string, string>>;
          step: MedicationFormWizardStep;
      };
