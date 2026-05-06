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

export type AppointmentStatusValue = 
    | 'scheduled'
    | 'done'
    | 'cancelled';

export type AppointmentDoneCommitPayload = {
    doctor_visit_summary: string | null;
};

export type AppointmentCancelledCommitPayload = {
    cancellation_reason: string | null;
};

export type Appointment = {
    id: number;
    doctor_type: AppointmentDoctorType;
    provider_name: string;
    address: string;
    starts_at: string;
    notes: string | null;
    doctor_visit_summary: string | null;
    cancellation_reason: string | null;
    status: AppointmentStatusValue;
};
