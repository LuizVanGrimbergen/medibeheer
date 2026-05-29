import type { FormDataErrors } from '@inertiajs/core';
import type { InertiaForm } from '@inertiajs/vue3';
import type {
    AppointmentDoctorType,
    AppointmentStatusValue,
} from '@/lib/types';

export type AppointmentFormState = {
    doctor_type: AppointmentDoctorType | '';
    provider_name: string;
    street: string;
    house_number: string;
    postal_code: string;
    city: string;
    starts_at_date: string;
    starts_at_time: string;
    notes: string;
    needs_transport: boolean;
    transport_family_ids: number[];
    status: AppointmentStatusValue;
};

type AppointmentFormInertiaErrors = FormDataErrors<AppointmentFormState> & {
    starts_at?: string;
};

export type AppointmentFormWithErrors = Omit<
    InertiaForm<AppointmentFormState>,
    'errors'
> & {
    errors: AppointmentFormInertiaErrors;
};
