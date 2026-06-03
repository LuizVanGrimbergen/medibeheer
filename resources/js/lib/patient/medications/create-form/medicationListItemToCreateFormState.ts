import type { MedicationCreateFormState } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { parseMedicationStrengthFromStored } from '@/lib/patient/medications/strength/parseMedicationStrengthFromStored';
import type { MedicationListItem } from '@/lib/types';
import {
    buildMedicationScheduleDoseTimeSlots,
    buildMedicationScheduleSnoozeTimeSlots,
} from '../schedule/medicationScheduleDoseTimes';
import { parseMedicationTimesPerDayCount } from '../validation/medicationFormValidationPrimitives';
import { blankMedicationCreateForm } from './medicationCreateFormDefaults';

export function medicationListItemToCreateFormState(
    item: MedicationListItem,
): MedicationCreateFormState {
    const base = blankMedicationCreateForm();
    const first = item.schedules[0];

    base.name = item.name;
    base.dose = item.dose?.trim() ?? '';
    base.dose_unit = item.dose_unit ?? base.dose_unit;
    base.type_medication = item.type_medication;
    const parsedStrength = parseMedicationStrengthFromStored(item.strength);
    base.strength = parsedStrength.strength;
    base.strength_amount = parsedStrength.strength_amount;
    base.strength_unit = parsedStrength.strength_unit;
    base.note = item.note ?? '';

    if (item.stock_pieces_per_package !== null && item.stock_pieces_per_package > 0) {
        base.stock_pieces_per_package = String(item.stock_pieces_per_package);
    }

    const firstStock = item.stocks[0];

    if (firstStock !== undefined) {
        base.current_stock = firstStock.current_stock.trim();
    }

    if (first === undefined) {
        return base;
    }

    const count = parseMedicationTimesPerDayCount(first.times_per_day);
    const slotCount = count ?? 1;
    const dose_time_slots = buildMedicationScheduleDoseTimeSlots(first.dose_time, slotCount);
    const snooze_time_slots = buildMedicationScheduleSnoozeTimeSlots(first.snooze_time, slotCount);

    base.schedule = {
        meal_timing: first.meal_timing,
        intake_frequency: first.intake_frequency,
        intake_weekdays: first.intake_weekdays === null ? [] : [...first.intake_weekdays],
        times_per_day: first.times_per_day,
        dose_time_slots,
        snooze_time_slots,
        start_date: first.start_date ?? '',
        end_date: first.end_date ?? '',
    };

    return base;
}
