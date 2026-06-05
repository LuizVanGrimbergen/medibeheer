import type { RequestPayload } from '@inertiajs/core';
import { z } from 'zod';
import type { AppointmentDoctorType } from '@/lib/types';
import {
    APPOINTMENT_DOCTOR_TYPE_VALUES,
    APPOINTMENT_STATUS_VALUES,
} from '@/lib/types';
import appointmentsNl from '@/translations/nl/patient/appointments';
import {
    collectAppointmentAddressFieldErrors,
    isAppointmentAddressValidationRequired,
    isBelgianPostalCodeValid,
} from '../appointmentAddressValidation';
import { parseLocalAppointmentDateTime } from '../validation/appointmentStartsAtLocalValidation';

export const PATIENT_APPOINTMENT_DOCTOR_TYPE_OPTIONS: AppointmentDoctorType[] =
    [...APPOINTMENT_DOCTOR_TYPE_VALUES];

const appointmentDoctorTypeValueSchema = z.enum(APPOINTMENT_DOCTOR_TYPE_VALUES);

const appointmentStatusValueSchema = z.enum(APPOINTMENT_STATUS_VALUES);

const patientAppointmentDialogFormFieldsSchema = z.object({
    doctor_type: z.union([z.literal(''), appointmentDoctorTypeValueSchema]),
    provider_name: z.string().trim().max(255),
    street: z.string().trim().max(500),
    house_number: z.string().trim().max(32),
    postal_code: z.string().trim().max(32),
    city: z.string().trim().max(255),
    starts_at_date: z
        .string()
        .trim()
        .min(1, appointmentsNl.stepValidation.startsAtDateRequired),
    starts_at_time: z
        .string()
        .trim()
        .min(1, appointmentsNl.stepValidation.startsAtTimeRequired),
    notes: z.string().max(10000),
    needs_transport: z.boolean(),
    transport_family_ids: z.array(z.number().int()),
    status: appointmentStatusValueSchema,
});

type PatientAppointmentDialogFormParsed = z.infer<
    typeof patientAppointmentDialogFormFieldsSchema
>;

function superRefinePatientAppointmentDialogForm(
    permitPastStartsAtIfSameInstantMs?: number,
): (data: PatientAppointmentDialogFormParsed, ctx: z.RefinementCtx) => void {
    return (data, ctx) => {
        if (data.doctor_type === '') {
            ctx.addIssue({
                code: 'custom',
                path: ['doctor_type'],
                message: appointmentsNl.stepValidation.doctorTypeRequired,
            });
        }

        if (data.needs_transport && data.transport_family_ids.length === 0) {
            ctx.addIssue({
                code: 'custom',
                path: ['transport_family_ids'],
                message:
                    appointmentsNl.stepValidation.transportRecipientsRequired,
            });
        }

        if (
            isAppointmentAddressValidationRequired(data.needs_transport, {
                street: data.street,
                postal_code: data.postal_code,
                city: data.city,
                house_number: data.house_number,
            })
        ) {
            const addressErrors = collectAppointmentAddressFieldErrors({
                street: data.street,
                postal_code: data.postal_code,
                city: data.city,
            });

            for (const [field, message] of Object.entries(addressErrors)) {
                if (message === undefined || message.length < 1) {
                    continue;
                }

                ctx.addIssue({
                    code: 'custom',
                    path: [field],
                    message,
                });
            }
        } else {
            const postalTrimmed = data.postal_code.trim();

            if (
                postalTrimmed.length > 0 &&
                !isBelgianPostalCodeValid(postalTrimmed)
            ) {
                ctx.addIssue({
                    code: 'custom',
                    path: ['postal_code'],
                    message: appointmentsNl.validation.postalCodeBelgianInvalid,
                });
            }
        }

        const dateTrimmed = data.starts_at_date.trim();
        const timeTrimmed = data.starts_at_time.trim();

        if (dateTrimmed.length < 1 || timeTrimmed.length < 1) {
            return;
        }

        const parsed = parseLocalAppointmentDateTime(dateTrimmed, timeTrimmed);

        if (parsed === null) {
            const reparsedDateOnly = parseLocalAppointmentDateTime(
                dateTrimmed,
                '12:00',
            );

            if (reparsedDateOnly === null) {
                ctx.addIssue({
                    code: 'custom',
                    path: ['starts_at_date'],
                    message: appointmentsNl.validation.startsAtDateInvalid,
                });

                return;
            }

            ctx.addIssue({
                code: 'custom',
                path: ['starts_at_time'],
                message: appointmentsNl.validation.startsAtTimeInvalid,
            });

            return;
        }

        const permitMs = permitPastStartsAtIfSameInstantMs;

        if (permitMs !== undefined && parsed.getTime() === permitMs) {
            return;
        }

        if (parsed.getTime() < Date.now()) {
            ctx.addIssue({
                code: 'custom',
                path: ['starts_at'],
                message: appointmentsNl.validation.startsAtMustNotBeInPast,
            });
        }
    };
}

function createPatientAppointmentDialogFormSchema(
    options?: PatientAppointmentFormPermitPastStartsAtOptions,
) {
    return patientAppointmentDialogFormFieldsSchema.superRefine(
        superRefinePatientAppointmentDialogForm(
            options?.permitPastStartsAtIfSameInstantMs,
        ),
    );
}

export type PatientAppointmentDialogFormValues = z.input<
    typeof patientAppointmentDialogFormFieldsSchema
>;

export type PatientAppointmentFormPermitPastStartsAtOptions = {
    permitPastStartsAtIfSameInstantMs?: number;
};

export function getPatientAppointmentDialogFormFieldErrors(
    snapshot: PatientAppointmentDialogFormValues,
    options?: PatientAppointmentFormPermitPastStartsAtOptions,
): Partial<Record<string, string>> {
    const result =
        createPatientAppointmentDialogFormSchema(options).safeParse(snapshot);

    if (result.success) {
        return {};
    }

    const out: Partial<Record<string, string>> = {};

    for (const issue of result.error.issues) {
        const head = issue.path[0];

        if (typeof head !== 'string' || issue.message.length < 1) {
            continue;
        }

        if (out[head] !== undefined) {
            continue;
        }

        out[head] = issue.message;
    }

    return out;
}

function buildRequestPayloadFromValidatedForm(
    data: PatientAppointmentDialogFormParsed,
): RequestPayload {
    if (data.doctor_type === '') {
        throw new Error(
            'Appointment form passed validation with an empty doctor type.',
        );
    }

    const {
        starts_at_date: startsAtDate,
        starts_at_time: startsAtTime,
        notes,
        transport_family_ids: transportFamilyIds,
        needs_transport: needsTransport,
        doctor_type: doctorType,
        provider_name: providerName,
        street,
        house_number: houseNumber,
        postal_code: postalCode,
        city,
        status,
    } = data;

    const payload: RequestPayload = {
        doctor_type: doctorType,
        provider_name: providerName,
        street,
        house_number: houseNumber,
        postal_code: postalCode,
        city,
        needs_transport: needsTransport,
        status,
        starts_at:
            startsAtDate && startsAtTime
                ? `${startsAtDate}T${startsAtTime}`
                : '',
        notes: notes === '' ? null : notes,
    };

    if (needsTransport) {
        payload.transport_family_ids = transportFamilyIds;
    }

    return payload;
}

export function patientAppointmentFormValuesToRequestPayload(
    values: Record<string, unknown>,
    options?: PatientAppointmentFormPermitPastStartsAtOptions,
): RequestPayload {
    const parsed =
        createPatientAppointmentDialogFormSchema(options).parse(values);

    return buildRequestPayloadFromValidatedForm(parsed);
}
