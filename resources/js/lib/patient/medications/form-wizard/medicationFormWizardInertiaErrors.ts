import type {
    MedicationCreateFormWithErrors,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';

export function medicationWizardStepForInertiaFormErrors(
    errors: MedicationCreateFormWithErrors['errors'],
): MedicationFormWizardStep | null {
    const hasDetailServerError =
        (errors.name !== undefined && errors.name.length > 0) ||
        (errors.dose !== undefined && errors.dose.length > 0) ||
        (errors.dose_unit !== undefined && errors.dose_unit.length > 0) ||
        (errors.type_medication !== undefined && errors.type_medication.length > 0);

    if (hasDetailServerError) {
        return 1;
    }

    const mealTimingError = errors['schedule.meal_timing'];
    const intakeFrequencyError = errors['schedule.intake_frequency'];
    const intakeWeekdaysError = errors['schedule.intake_weekdays'];
    const hasStepTwoServerError =
        (mealTimingError !== undefined && mealTimingError.length > 0) ||
        (intakeFrequencyError !== undefined && intakeFrequencyError.length > 0) ||
        (intakeWeekdaysError !== undefined && intakeWeekdaysError.length > 0);

    if (hasStepTwoServerError) {
        return 2;
    }

    const timesPerDayError = errors['schedule.times_per_day'];
    const doseTimeError = errors['schedule.dose_time'];

    if (timesPerDayError !== undefined && timesPerDayError.length > 0) {
        return 3;
    }

    if (doseTimeError !== undefined && doseTimeError.length > 0) {
        return 4;
    }

    const startDateError = errors['schedule.start_date'];
    const endDateError = errors['schedule.end_date'];

    if (
        (startDateError !== undefined && startDateError.length > 0) ||
        (endDateError !== undefined && endDateError.length > 0)
    ) {
        return 5;
    }

    const noteError = errors.note;
    const currentStockError = errors.current_stock;
    const lowStockError = errors.low_stock;

    if (
        (noteError !== undefined && noteError.length > 0) ||
        (currentStockError !== undefined && currentStockError.length > 0) ||
        (lowStockError !== undefined && lowStockError.length > 0)
    ) {
        return 6;
    }

    return null;
}
