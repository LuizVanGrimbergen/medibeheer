import type { LucideIcon } from 'lucide-vue-next';
import { AlertCircle, Check, Minus } from 'lucide-vue-next';
import type { MedicationIntakeDayStatusValue } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';

export const MEDICATION_INTAKE_DAY_ICON_STATUS_VALUES = [
    'complete',
    'partial',
    'none_taken',
] as const;

export type MedicationIntakeDayIconStatusValue =
    (typeof MEDICATION_INTAKE_DAY_ICON_STATUS_VALUES)[number];

export type MedicationIntakeDayPresentation = {
    status: MedicationIntakeDayIconStatusValue;
    icon: LucideIcon;
    faceClass: string;
    labelKey: `patient.medications.history.status.${MedicationIntakeDayIconStatusValue}`;
};

const MEDICATION_INTAKE_DAY_ICON: Record<
    MedicationIntakeDayIconStatusValue,
    LucideIcon
> = {
    complete: Check,
    partial: Minus,
    none_taken: AlertCircle,
};

type MedicationIntakeDayPresentationFields = Pick<
    MedicationIntakeDayPresentation,
    'faceClass' | 'labelKey'
>;

const MEDICATION_INTAKE_DAY_PRESENTATION: Record<
    MedicationIntakeDayIconStatusValue,
    MedicationIntakeDayPresentationFields
> = {
    complete: {
        faceClass: 'text-success',
        labelKey: 'patient.medications.history.status.complete',
    },
    partial: {
        faceClass: 'text-warning',
        labelKey: 'patient.medications.history.status.partial',
    },
    none_taken: {
        faceClass: 'text-danger',
        labelKey: 'patient.medications.history.status.none_taken',
    },
};

export const medicationIntakeDayIcon = (
    status: MedicationIntakeDayIconStatusValue,
): LucideIcon => MEDICATION_INTAKE_DAY_ICON[status];

export const medicationIntakeDayFaceClass = (
    status: MedicationIntakeDayIconStatusValue,
): string => MEDICATION_INTAKE_DAY_PRESENTATION[status].faceClass;

export const medicationIntakeDayPresentation = (
    status: MedicationIntakeDayIconStatusValue,
): MedicationIntakeDayPresentation => ({
    status,
    icon: MEDICATION_INTAKE_DAY_ICON[status],
    ...MEDICATION_INTAKE_DAY_PRESENTATION[status],
});

export const MEDICATION_INTAKE_DAY_LEGEND_OPTIONS: MedicationIntakeDayPresentation[] =
    MEDICATION_INTAKE_DAY_ICON_STATUS_VALUES.map((status) =>
        medicationIntakeDayPresentation(status),
    );

export function isMedicationIntakeDayIconStatus(
    status: MedicationIntakeDayStatusValue,
): status is MedicationIntakeDayIconStatusValue {
    return (
        MEDICATION_INTAKE_DAY_ICON_STATUS_VALUES as readonly string[]
    ).includes(status);
}
