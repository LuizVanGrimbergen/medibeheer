import type { MedicationCreateFormState } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import type { MedicationListItem } from '@/lib/types';
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
    base.color = item.color ?? base.color;
    base.note = item.note ?? '';

    const firstStock = item.stocks[0];

    if (firstStock !== undefined) {
        base.current_stock = firstStock.current_stock.trim();
        base.low_stock = firstStock.low_stock.trim();
    }

    if (first === undefined) {
        return base;
    }

    const rawSlots = first.dose_time.split(',').map((segment) => segment.trim());
    const slots = rawSlots.length > 0 ? rawSlots : [''];
    const count = parseMedicationTimesPerDayCount(first.times_per_day);

    let dose_time_slots = slots;

    if (count !== null) {
        if (slots.length < count) {
            dose_time_slots = [
                ...slots,
                ...Array.from({ length: count - slots.length }, () => ''),
            ];
        }

        if (slots.length > count) {
            dose_time_slots = slots.slice(0, count);
        }
    }

    base.schedule = {
        meal_timing: first.meal_timing,
        intake_frequency: first.intake_frequency,
        intake_weekdays: first.intake_weekdays === null ? [] : [...first.intake_weekdays],
        times_per_day: first.times_per_day,
        dose_time_slots,
        start_date: first.start_date ?? '',
        end_date: first.end_date ?? '',
    };

    return base;
}
