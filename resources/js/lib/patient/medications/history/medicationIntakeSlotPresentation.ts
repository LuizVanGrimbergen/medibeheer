import type { LucideIcon } from 'lucide-vue-next';
import { AlertCircle, Check, Clock } from 'lucide-vue-next';
import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import { medicationUrgencyToneClasses } from '@/lib/patient/medications/urgency/medicationUrgencyToneClasses';

const MEDICATION_INTAKE_TIMEZONE = 'Europe/Amsterdam';

const lateToneClasses = medicationUrgencyToneClasses('warning');

export const MEDICATION_INTAKE_SLOT_STATUS_VALUES = [
    'taken',
    'late',
    'not_taken',
] as const;

export type MedicationIntakeSlotStatusValue =
    (typeof MEDICATION_INTAKE_SLOT_STATUS_VALUES)[number];

export type MedicationIntakeSlotPresentation = {
    status: MedicationIntakeSlotStatusValue;
    icon: LucideIcon;
    iconWrapperClass: string;
    iconToneClass: string;
    tagClass: string;
    labelKey: `patient.medications.history.slot.${MedicationIntakeSlotStatusValue}`;
};

type MedicationIntakeSlotPresentationFields = Pick<
    MedicationIntakeSlotPresentation,
    'icon' | 'iconWrapperClass' | 'iconToneClass' | 'tagClass' | 'labelKey'
>;

const MEDICATION_INTAKE_SLOT_PRESENTATION: Record<
    MedicationIntakeSlotStatusValue,
    MedicationIntakeSlotPresentationFields
> = {
    taken: {
        icon: Check,
        iconWrapperClass: 'bg-success/10',
        iconToneClass: 'text-success',
        tagClass: 'bg-success/12 text-success',
        labelKey: 'patient.medications.history.slot.taken',
    },
    late: {
        icon: Clock,
        iconWrapperClass: lateToneClasses.pillWrap,
        iconToneClass: lateToneClasses.pillIcon,
        tagClass: `${lateToneClasses.pillWrap} ${lateToneClasses.pillIcon}`,
        labelKey: 'patient.medications.history.slot.late',
    },
    not_taken: {
        icon: AlertCircle,
        iconWrapperClass: 'bg-danger/10',
        iconToneClass: 'text-danger',
        tagClass: 'bg-danger/12 text-danger',
        labelKey: 'patient.medications.history.slot.not_taken',
    },
};

function parseDoseTimeMinutes(value: string): number | null {
    const match = /^(\d{1,2}):(\d{2})$/.exec(value.trim());

    if (match === null) {
        return null;
    }

    const hours = Number(match[1]);
    const minutes = Number(match[2]);

    if (hours > 23 || minutes > 59) {
        return null;
    }

    return hours * 60 + minutes;
}

function takenAtMinutesInTimezone(takenAtIso: string): number | null {
    const takenAt = new Date(takenAtIso);

    if (Number.isNaN(takenAt.getTime())) {
        return null;
    }

    const formatter = new Intl.DateTimeFormat('en-GB', {
        timeZone: MEDICATION_INTAKE_TIMEZONE,
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });

    const parts = formatter.formatToParts(takenAt);
    const read = (type: Intl.DateTimeFormatPartTypes): number =>
        Number(parts.find((part) => part.type === type)?.value ?? '0');

    return read('hour') * 60 + read('minute');
}

export function isMedicationIntakeTakenWithinWindow(
    slot: Pick<
        MedicationIntakeHistorySlot,
        'dose_time' | 'snooze_minutes' | 'taken_at'
    >,
): boolean {
    if (slot.taken_at === null) {
        return false;
    }

    const doseStartMinutes = parseDoseTimeMinutes(slot.dose_time);

    if (doseStartMinutes === null) {
        return true;
    }

    const takenMinutes = takenAtMinutesInTimezone(slot.taken_at);

    if (takenMinutes === null) {
        return true;
    }

    const windowEndMinutes = doseStartMinutes + slot.snooze_minutes;

    return takenMinutes >= doseStartMinutes && takenMinutes <= windowEndMinutes;
}

export function resolveMedicationIntakeSlotStatus(
    slot: Pick<
        MedicationIntakeHistorySlot,
        'dose_time' | 'snooze_minutes' | 'taken_at'
    >,
): MedicationIntakeSlotStatusValue {
    if (slot.taken_at === null) {
        return 'not_taken';
    }

    if (isMedicationIntakeTakenWithinWindow(slot)) {
        return 'taken';
    }

    return 'late';
}

export function medicationIntakeSlotPresentation(
    status: MedicationIntakeSlotStatusValue,
): MedicationIntakeSlotPresentation {
    return {
        status,
        ...MEDICATION_INTAKE_SLOT_PRESENTATION[status],
    };
}

export function medicationIntakeSlotPresentationForSlot(
    slot: Pick<
        MedicationIntakeHistorySlot,
        'dose_time' | 'snooze_minutes' | 'taken_at'
    >,
): MedicationIntakeSlotPresentation {
    return medicationIntakeSlotPresentation(
        resolveMedicationIntakeSlotStatus(slot),
    );
}
