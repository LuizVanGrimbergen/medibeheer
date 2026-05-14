import type { MedicationCreateFormState } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { MEDICATION_COLOR_HEX_VALUES } from '@/lib/types';

export function blankMedicationCreateForm(): MedicationCreateFormState {
    return {
        name: '',
        dose: '',
        dose_unit: '',
        type_medication: '',
        color: MEDICATION_COLOR_HEX_VALUES[0],
        current_stock: '',
        low_stock: '',
        note: '',
        schedule: {
            meal_timing: '',
            intake_frequency: '',
            intake_weekdays: [],
            times_per_day: '',
            dose_time_slots: [''],
            start_date: '',
            end_date: '',
        },
    };
}
