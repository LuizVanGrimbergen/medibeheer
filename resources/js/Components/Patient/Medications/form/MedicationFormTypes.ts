import type { FormDataErrors } from '@inertiajs/core';
import type { InertiaForm } from '@inertiajs/vue3';
import type {
    MedicationDoseUnitValue,
    MedicationIntakeFrequencyValue,
    MedicationMealTimingValue,
    MedicationTypeValue,
} from '@/lib/types';

export type MedicationScheduleFormSlice = {
    meal_timing: MedicationMealTimingValue | '';
    intake_frequency: MedicationIntakeFrequencyValue | '';
    intake_weekdays: number[];
    times_per_day: string;
    dose_time_slots: string[];
    start_date: string;
    end_date: string;
};

export type MedicationFormWizardStep = 1 | 2 | 3 | 4 | 5 | 6 | 7;

export type MedicationCreateFormState = {
    name: string;
    dose: string;
    dose_unit: MedicationDoseUnitValue | '';
    type_medication: MedicationTypeValue | '';
    current_stock: string;
    low_stock: string;
    note: string;
    schedule: MedicationScheduleFormSlice;
};

type MedicationScheduleFormErrorKeys =
    | 'schedule.meal_timing'
    | 'schedule.intake_frequency'
    | 'schedule.intake_weekdays'
    | 'schedule.times_per_day'
    | 'schedule.dose_time'
    | 'schedule.start_date'
    | 'schedule.end_date';

type MedicationCreateFormInertiaErrors = FormDataErrors<MedicationCreateFormState> &
    Partial<Record<MedicationScheduleFormErrorKeys, string>>;

export type MedicationCreateFormWithErrors = Omit<
    InertiaForm<MedicationCreateFormState>,
    'errors'
> & {
    errors: MedicationCreateFormInertiaErrors;
};
