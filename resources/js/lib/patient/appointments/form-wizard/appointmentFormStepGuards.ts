import appointmentsNl from '@/translations/nl/patient/appointments';
import { parseLocalAppointmentDateTime } from '../validation/appointmentStartsAtLocalValidation';

export type AppointmentFormStepId =
    | 'provider'
    | 'address'
    | 'schedule'
    | 'transport'
    | 'notes';

export const APPOINTMENT_FORM_STEP_ORDER: AppointmentFormStepId[] = [
    'provider',
    'address',
    'schedule',
    'transport',
    'notes',
];

export type AppointmentFormInlineStepErrorKey =
    | 'doctor_type'
    | 'provider_name'
    | 'street'
    | 'postal_code'
    | 'city'
    | 'starts_at_date'
    | 'starts_at_time'
    | 'starts_at'
    | 'transport_family_ids';

export type AppointmentFormInlineStepErrors = Partial<
    Record<AppointmentFormInlineStepErrorKey, string>
>;

type StepSnapshot = {
    doctor_type: string;
    provider_name: string;
    street: string;
    postal_code: string;
    city: string;
    starts_at_date: string;
    starts_at_time: string;
    needs_transport: boolean;
    transport_family_ids: number[];
};

export function appointmentFormStepClientValidatedFieldKeys(
    step: AppointmentFormStepId,
): readonly string[] {
    if (step === 'provider') {
        return ['doctor_type', 'provider_name'] as const;
    }

    if (step === 'address') {
        return ['street', 'house_number', 'postal_code', 'city'] as const;
    }

    if (step === 'schedule') {
        return ['starts_at_date', 'starts_at_time', 'starts_at'] as const;
    }

    if (step === 'transport') {
        return ['transport_family_ids'] as const;
    }

    if (step === 'notes') {
        return ['notes', 'status'] as const;
    }

    return [] as const;
}

export function firstAppointmentFormStepContainingFieldErrors(
    fieldErrors: Partial<Record<string, string>>,
    wizardStepOrder: AppointmentFormStepId[],
): AppointmentFormStepId | null {
    for (const stepId of wizardStepOrder) {
        const keys = appointmentFormStepClientValidatedFieldKeys(stepId);

        if (
            keys.some((key) => {
                const message = fieldErrors[key];

                return message !== undefined && message.length > 0;
            })
        ) {
            return stepId;
        }
    }

    return null;
}

function fieldErrorsForProviderStep(values: StepSnapshot): AppointmentFormInlineStepErrors {
    const out: AppointmentFormInlineStepErrors = {};

    if (values.doctor_type === '') {
        out.doctor_type = appointmentsNl.stepValidation.doctorTypeRequired;
    }

    if (values.provider_name.trim().length < 1) {
        out.provider_name = appointmentsNl.stepValidation.providerNameRequired;
    }

    return out;
}

function fieldErrorsForAddressStep(values: StepSnapshot): AppointmentFormInlineStepErrors {
    const out: AppointmentFormInlineStepErrors = {};

    if (values.street.trim().length < 1) {
        out.street = appointmentsNl.stepValidation.streetRequired;
    }

    const postalTrimmed = values.postal_code.trim();

    if (postalTrimmed.length < 1) {
        out.postal_code = appointmentsNl.stepValidation.postalCodeRequired;
    }

    if (postalTrimmed.length > 0 && postalTrimmed.length < 4) {
        out.postal_code = appointmentsNl.validation.postalCodeMinLength;
    }

    if (values.city.trim().length < 1) {
        out.city = appointmentsNl.stepValidation.cityRequired;
    }

    return out;
}

function fieldErrorsForScheduleStep(
    values: StepSnapshot,
    options?: { permitPastStartsAtIfSameInstantMs?: number },
): AppointmentFormInlineStepErrors {
    const out: AppointmentFormInlineStepErrors = {};
    const dateTrimmed = values.starts_at_date.trim();
    const timeTrimmed = values.starts_at_time.trim();

    if (dateTrimmed.length < 1) {
        out.starts_at_date = appointmentsNl.stepValidation.startsAtDateRequired;
    }

    if (timeTrimmed.length < 1) {
        out.starts_at_time = appointmentsNl.stepValidation.startsAtTimeRequired;
    }

    if (Object.keys(out).length > 0) {
        return out;
    }

    const parsed = parseLocalAppointmentDateTime(dateTrimmed, timeTrimmed);

    if (parsed === null) {
        const reparsedDateOnly = parseLocalAppointmentDateTime(dateTrimmed, '12:00');

        if (reparsedDateOnly === null) {
            out.starts_at_date = appointmentsNl.validation.startsAtDateInvalid;
        } else {
            out.starts_at_time = appointmentsNl.validation.startsAtTimeInvalid;
        }

        return out;
    }

    const permitMs = options?.permitPastStartsAtIfSameInstantMs;

    if (permitMs !== undefined && parsed.getTime() === permitMs) {
        return out;
    }

    if (parsed.getTime() < Date.now()) {
        out.starts_at = appointmentsNl.validation.startsAtMustNotBeInPast;
    }

    return out;
}

function fieldErrorsForTransportStep(values: StepSnapshot): AppointmentFormInlineStepErrors {
    const out: AppointmentFormInlineStepErrors = {};

    if (values.needs_transport && values.transport_family_ids.length < 1) {
        out.transport_family_ids = appointmentsNl.stepValidation.transportRecipientsRequired;
    }

    return out;
}

export function collectAppointmentFormStepValidationFieldErrors(
    step: AppointmentFormStepId,
    values: StepSnapshot,
    options?: { permitPastStartsAtIfSameInstantMs?: number },
): AppointmentFormInlineStepErrors {
    if (step === 'provider') {
        return fieldErrorsForProviderStep(values);
    }

    if (step === 'address') {
        return fieldErrorsForAddressStep(values);
    }

    if (step === 'schedule') {
        return fieldErrorsForScheduleStep(values, options);
    }

    if (step === 'transport') {
        return fieldErrorsForTransportStep(values);
    }

    return {};
}
