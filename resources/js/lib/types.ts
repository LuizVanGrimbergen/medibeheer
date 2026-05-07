import type { Component } from 'vue';

export type RoleKey = 'patient' | 'doctor' | 'family_member';

export type RoleOption = {
    key: RoleKey;
    label: string;
    icon: Component;
    ringClass: string;
};

export type User = {
    public_id: string;
    name: string;
    email: string | null;
    role: RoleKey;
    email_verified_at: string | null;
};



export type Auth = {
    user: User | null;
};

export type FamilyDashboardProps = {
    has_linked_patient: boolean;
    active_patient_id: number | null;
    patients: {
        id: number;
        name: string;
        switch_url: string;
        is_active: boolean;
    }[];
};

export type PendingFamilyInvitation = {
    id: number;
    invited_email: string;
    expires_at: string;
    revoke_url: string;
};

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: Auth;
    flash: {
        error: string | null;
        success: string | null;
        rateLimitSeconds: number | null;
    };
};

export type AppointmentDoctorType =
    | 'dentist'
    | 'hospital'
    | 'general_practitioner'
    | 'specialist'

export const APPOINTMENT_DOCTOR_TYPE_VALUES = [
    'dentist',
    'hospital',
    'general_practitioner',
    'specialist',
] as const;

export const APPOINTMENT_STATUS_VALUES = [
    'scheduled',
    'done',
    'cancelled',
] as const;

export type AppointmentStatusValue = (typeof APPOINTMENT_STATUS_VALUES)[number];

export type AppointmentTransportStatusValue =
    | 'requested'
    | 'accepted'
    | 'declined';

export type AppointmentDoneCommitPayload = {
    doctor_visit_summary: string | null;
};

export type AppointmentCancelledCommitPayload = {
    cancellation_reason: string | null;
};

export type PaginationMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

export type Paginated<T> = {
    data: T[];
    meta: PaginationMeta;
};

export type Appointment = {
    id: number;
    doctor_type: AppointmentDoctorType;
    provider_name: string;
    address: string;
    starts_at: string;
    needs_transport: boolean;
    transport_status: AppointmentTransportStatusValue | null;
    transport_invited_family_ids: number[];
    transport_family: {
        id: number;
        name: string;
    } | null;
    notes: string | null;
    doctor_visit_summary: string | null;
    cancellation_reason: string | null;
    status: AppointmentStatusValue;
};
