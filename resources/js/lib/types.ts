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

export type PendingFamilyInvitation = {
    id: number;
    invited_email: string;
    expires_at: string;
    revoke_url: string;
};

export const DAILY_MOOD_SCORE_VALUES = [
    'bad',
    'ok',
    'good',
] as const;

export type DailyMoodScoreValue = (typeof DAILY_MOOD_SCORE_VALUES)[number];

export type FamilyDashboardProps = {
    has_linked_patient: boolean;
    active_patient_id: number | null;
    active_patient_today_mood: DailyMoodScoreValue | null;
    patients: {
        id: number;
        name: string;
        switch_url: string;
        is_active: boolean;
    }[];
};

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: Auth;
    flash: {
        error: string | null;
        success: string | null;
        rateLimitSeconds: number | null;
        daily_checkin_mood: DailyMoodScoreValue | null;
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

export const DAILY_CHECKIN_SYMPTOM_VALUES = [
    'pain',
    'fatigue',
    'dizziness',
    'shortness_of_breath',
    'nausea',
    'poor_sleep',
    'loneliness',
    'anxiety_or_worry',
    'poor_appetite',
    'stiff_or_joint_pain',
] as const;

export type DailyCheckinSymptomValue =
    (typeof DAILY_CHECKIN_SYMPTOM_VALUES)[number];

export type DailyCheckin = {
    id: number;
    checkin_date: string;
    mood_score: DailyMoodScoreValue;
    symptoms: DailyCheckinSymptomValue[] | null;
    note: string | null;
    created_at: string;
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
    street: string;
    house_number: string;
    postal_code: string;
    city: string;
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
