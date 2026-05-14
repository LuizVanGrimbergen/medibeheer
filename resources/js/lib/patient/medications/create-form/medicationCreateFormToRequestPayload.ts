import type { MedicationCreateFormState } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { parseMedicationTimesPerDayCount } from '../validation/medicationFormValidationPrimitives';

function buildMedicationScheduleDoseTimeForPayload(
    schedule: MedicationCreateFormState['schedule'],
): string {
    const count = parseMedicationTimesPerDayCount(schedule.times_per_day);

    if (count === null) {
        return '';
    }

    return schedule.dose_time_slots
        .slice(0, count)
        .map((slot) => slot.trim())
        .join(', ');
}

export function medicationCreateFormStateToRequestPayload(data: MedicationCreateFormState): {
    name: string;
    dose: string;
    dose_unit: MedicationCreateFormState['dose_unit'];
    type_medication: MedicationCreateFormState['type_medication'];
    color: MedicationCreateFormState['color'];
    current_stock: string;
    low_stock: string;
    note: string | null;
    schedule: {
        meal_timing: MedicationCreateFormState['schedule']['meal_timing'];
        intake_frequency: MedicationCreateFormState['schedule']['intake_frequency'];
        intake_weekdays: number[] | null;
        times_per_day: string;
        dose_quantity: string;
        dose_time: string;
        start_date: string;
        end_date: string;
    };
} {
    const noteTrimmed = data.note.trim();

    return {
        name: data.name.trim(),
        dose: data.dose.trim(),
        dose_unit: data.dose_unit,
        type_medication: data.type_medication,
        color: data.color,
        current_stock: data.current_stock.trim(),
        low_stock: data.low_stock.trim(),
        note: noteTrimmed === '' ? null : noteTrimmed,
        schedule: {
            meal_timing: data.schedule.meal_timing,
            intake_frequency: data.schedule.intake_frequency,
            intake_weekdays:
                data.schedule.intake_frequency === 'weekdays'
                    ? [...data.schedule.intake_weekdays].sort((a, b) => a - b)
                    : null,
            times_per_day: data.schedule.times_per_day.trim(),
            dose_quantity: data.dose.trim(),
            dose_time: buildMedicationScheduleDoseTimeForPayload(data.schedule),
            start_date: data.schedule.start_date.trim(),
            end_date: data.schedule.end_date.trim(),
        },
    };
}
