import { nextTick } from 'vue';
import {
    medicationFormErrorScrollOrder,
    medicationFormFieldDomSuffix,
} from './medicationFormWizardConstants';

export function scrollMedicationFormFirstFieldErrorIntoView(
    idPrefix: string,
    fieldErrors: Partial<Record<string, string>>,
): void {
    void nextTick(() => {
        for (const key of medicationFormErrorScrollOrder) {
            const message = fieldErrors[key];

            if (message === undefined || message.length < 1) {
                continue;
            }

            const suffix = medicationFormFieldDomSuffix[key];

            document
                .getElementById(`${idPrefix}-${suffix}`)
                ?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            return;
        }
    });
}
