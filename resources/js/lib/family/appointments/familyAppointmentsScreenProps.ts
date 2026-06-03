import type { Appointment, FamilyDashboardProps, Paginated } from '@/lib/types';

export type FamilyAppointmentView = 'planned' | 'completed';

export type FamilyAppointment = Pick<
    Appointment,
    | 'id'
    | 'doctor_type'
    | 'provider_name'
    | 'street'
    | 'house_number'
    | 'postal_code'
    | 'city'
    | 'starts_at'
    | 'needs_transport'
    | 'transport_status'
    | 'transport_family'
    | 'doctor_visit_summary'
    | 'cancellation_reason'
    | 'status'
> & {
    transport_invitation: {
        id: number;
        invited_at: string | null;
        is_pending: boolean;
        accept_url: string | null;
        decline_url: string | null;
    } | null;
};

export type FamilyAppointmentsScreenProps = {
    family: FamilyDashboardProps;
    appointments: Paginated<FamilyAppointment>;
    appointment_view: FamilyAppointmentView;
    appointment_tab_totals: { planned: number; completed: number };
};
