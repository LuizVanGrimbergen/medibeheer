import { i18n } from '@/i18n';

export function medicationWizardStepValidation(key: string): string {
    return String(i18n.global.t(`patient.medications.stepValidation.${key}`));
}
