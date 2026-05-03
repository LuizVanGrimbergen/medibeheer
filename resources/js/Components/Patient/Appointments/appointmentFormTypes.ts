import type { FormDataErrors } from '@inertiajs/core';
import type { InertiaForm } from '@inertiajs/vue3';
import type {
    AppointmentDoctorType,
    AppointmentStatusValue,
} from '@/lib/types';

type AppointmentFormState = {
    doctor_type: AppointmentDoctorType | '';
    provider_name: string;
    address: string;
    starts_at_date: string;
    starts_at_time: string;
    notes: string;
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
