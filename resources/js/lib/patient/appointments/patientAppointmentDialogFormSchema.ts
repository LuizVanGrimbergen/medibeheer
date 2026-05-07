import type { RequestPayload } from '@inertiajs/core';
import { z } from 'zod';
import { APPOINTMENT_DOCTOR_TYPE_VALUES, APPOINTMENT_STATUS_VALUES } from '@/lib/types';
import type { AppointmentDoctorType } from '@/lib/types';

export const PATIENT_APPOINTMENT_DOCTOR_TYPE_OPTIONS: AppointmentDoctorType[] = [
    ...APPOINTMENT_DOCTOR_TYPE_VALUES,
];

const appointmentDoctorTypeValueSchema = z.enum(APPOINTMENT_DOCTOR_TYPE_VALUES);

const appointmentStatusValueSchema = z.enum(APPOINTMENT_STATUS_VALUES);

export const patientAppointmentDialogFormSchema = z
    .object({
        doctor_type: z.union([z.literal(''), appointmentDoctorTypeValueSchema]),
        provider_name: z.string().trim().min(1, 'Required').max(255),
        address: z.string().trim().min(1, 'Required').max(2000),
        starts_at_date: z.string().trim().min(1, 'Required'),
        starts_at_time: z.string().trim().min(1, 'Required'),
        notes: z.string().max(10000),
        needs_transport: z.boolean(),
        transport_family_ids: z.array(z.number().int()),
        status: appointmentStatusValueSchema,
    })
    .superRefine((data, ctx) => {
        if (data.doctor_type === '') {
            ctx.addIssue({
                code: 'custom',
                path: ['doctor_type'],
                message: 'Required',
            });
        }

        if (data.needs_transport && data.transport_family_ids.length === 0) {
            ctx.addIssue({
                code: 'custom',
                path: ['transport_family_ids'],
                message: 'Required when transport is enabled',
            });
        }
    });

export type PatientAppointmentDialogFormValues = z.input<typeof patientAppointmentDialogFormSchema>;

export function patientAppointmentDialogFormIsSubmittable(
    snapshot: PatientAppointmentDialogFormValues,
): boolean {
    return patientAppointmentDialogFormSchema.safeParse(snapshot).success;
}

function buildRequestPayloadFromValidatedForm(
    data: z.infer<typeof patientAppointmentDialogFormSchema>,
): RequestPayload {
    if (data.doctor_type === '') {
        throw new Error('Appointment form passed validation with an empty doctor type.');
    }

    const {
        starts_at_date: startsAtDate,
        starts_at_time: startsAtTime,
        notes,
        transport_family_ids: transportFamilyIds,
        needs_transport: needsTransport,
        doctor_type: doctorType,
        provider_name: providerName,
        address,
        status,
    } = data;

    const payload: RequestPayload = {
        doctor_type: doctorType,
        provider_name: providerName,
        address,
        needs_transport: needsTransport,
        status,
        starts_at:
            startsAtDate && startsAtTime ? `${startsAtDate}T${startsAtTime}` : '',
        notes: notes === '' ? null : notes,
    };

    if (needsTransport) {
        payload.transport_family_ids = transportFamilyIds;
    }

    return payload;
}

export function patientAppointmentFormValuesToRequestPayload(
    values: Record<string, unknown>,
): RequestPayload {
    const parsed = patientAppointmentDialogFormSchema.parse(values);

    return buildRequestPayloadFromValidatedForm(parsed);
}
