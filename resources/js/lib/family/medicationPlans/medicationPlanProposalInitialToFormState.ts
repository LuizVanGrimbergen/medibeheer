import type { MedicationCreateFormState } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { blankMedicationCreateForm } from '@/lib/patient/medications/create-form/medicationCreateFormDefaults';
import {
    buildMedicationScheduleDoseTimeSlots,
    buildMedicationScheduleSnoozeTimeSlots,
} from '@/lib/patient/medications/schedule/medicationScheduleDoseTimes';
import { parseMedicationStrengthFromStored } from '@/lib/patient/medications/strength/parseMedicationStrengthFromStored';
import { parseMedicationTimesPerDayCount } from '@/lib/patient/medications/validation/medicationFormValidationPrimitives';
import type {
    MedicationDoseUnitValue,
    MedicationIntakeFrequencyValue,
    MedicationMealTimingValue,
    MedicationTypeValue,
} from '@/lib/types';

export type MedicationPlanProposalFormInitial = {
    name: string;
    dose: string;
    dose_unit: string | null;
    type_medication: string | null;
    strength: string | null;
    note: string | null;
    current_stock: string;
    schedule: {
        meal_timing: string | null;
        intake_frequency: string;
        times_per_day: string;
        dose_time: string;
        snooze_time: string;
        start_date: string | null;
        end_date: string | null;
        intake_weekdays: number[] | null;
    } | null;
};

export function medicationPlanProposalInitialToFormState(
    initial: MedicationPlanProposalFormInitial,
): MedicationCreateFormState {
    const base = blankMedicationCreateForm();

    base.name = initial.name;
    base.dose = initial.dose?.trim() ?? '';
    base.dose_unit = (initial.dose_unit ?? base.dose_unit) as MedicationDoseUnitValue | '';
    base.type_medication = (initial.type_medication ?? base.type_medication) as MedicationTypeValue | '';
    const parsedStrength = parseMedicationStrengthFromStored(initial.strength);
    base.strength = parsedStrength.strength;
    base.strength_amount = parsedStrength.strength_amount;
    base.strength_unit = parsedStrength.strength_unit;
    base.note = initial.note ?? '';
    base.current_stock = initial.current_stock?.trim() ?? '';

    const schedule = initial.schedule;

    if (schedule === null) {
        return base;
    }

    const count = parseMedicationTimesPerDayCount(schedule.times_per_day);
    const slotCount = count ?? 1;

    base.schedule = {
        meal_timing: (schedule.meal_timing ?? '') as MedicationMealTimingValue | '',
        intake_frequency: schedule.intake_frequency as MedicationIntakeFrequencyValue | '',
        intake_weekdays: schedule.intake_weekdays === null ? [] : [...schedule.intake_weekdays],
        times_per_day: schedule.times_per_day,
        dose_time_slots: buildMedicationScheduleDoseTimeSlots(schedule.dose_time, slotCount),
        snooze_time_slots: buildMedicationScheduleSnoozeTimeSlots(schedule.snooze_time, slotCount),
        start_date: schedule.start_date ?? '',
        end_date: schedule.end_date ?? '',
    };

    return base;
}
