import type { MedicationCreateFormState } from '@/Components/Patient/Medications/form/MedicationFormTypes';

export function blankMedicationCreateForm(): MedicationCreateFormState {
    return {
        name: '',
        dose: '',
        dose_unit: '',
        type_medication: '',
        strength: '',
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
