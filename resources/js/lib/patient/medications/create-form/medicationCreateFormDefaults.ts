import type { MedicationCreateFormState } from '@/Components/Patient/Medications/form/MedicationFormTypes';

export function blankMedicationCreateForm(): MedicationCreateFormState {
    return {
        name: '',
        dose: '',
        dose_unit: '',
        type_medication: '',
        strength: '',
        strength_amount: '',
        strength_unit: '',
        current_stock: '',
        stock_pieces_per_package: '',
        note: '',
        schedule: {
            meal_timing: '',
            intake_frequency: '',
            intake_weekdays: [],
            times_per_day: '',
            dose_time_slots: [''],
            snooze_time_slots: ['30'],
            start_date: '',
            end_date: '',
        },
    };
}
