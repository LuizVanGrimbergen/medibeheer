export const MEDICATION_FORM_WIZARD_STEP_TOTAL = 7;

export const medicationFormErrorScrollOrder = [
    'name',
    'type_medication',
    'dose',
    'dose_unit',
    'strength',
    'strength_amount',
    'strength_unit',
    'current_stock',
    'stock_pieces_per_package',
    'note',
    'schedule.meal_timing',
    'schedule.intake_frequency',
    'schedule.intake_weekdays',
    'schedule.times_per_day',
    'schedule.dose_time',
    'prescription_expiry_date',
    'schedule.start_date',
    'schedule.end_date',
] as const;

export type MedicationFormWizardScrollFieldKey = (typeof medicationFormErrorScrollOrder)[number];

export const medicationFormFieldDomSuffix: Record<MedicationFormWizardScrollFieldKey, string> = {
    name: 'name',
    type_medication: 'type',
    dose: 'dose',
    dose_unit: 'dose-unit-select',
    strength: 'strength-amount',
    strength_amount: 'strength-amount',
    strength_unit: 'strength-unit-select',
    current_stock: 'stock-boxes',
    stock_pieces_per_package: 'stock-pieces-per-box',
    note: 'note',
    'schedule.meal_timing': 'schedule-meal-timing',
    'schedule.intake_frequency': 'schedule-intake-frequency',
    'schedule.intake_weekdays': 'schedule-intake-weekdays',
    'schedule.times_per_day': 'schedule-times-per-day',
    'schedule.dose_time': 'schedule-dose-time-0',
    prescription_expiry_date: 'prescription-expiry-date',
    'schedule.start_date': 'schedule-start-date',
    'schedule.end_date': 'schedule-end-date',
};
