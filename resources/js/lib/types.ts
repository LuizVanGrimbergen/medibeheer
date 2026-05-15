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

export const MEDICATION_TYPE_VALUES = [
    'pill',
    'liquid',
    'injection',
    'cream',
    'sachets',
    'other',
] as const;

export type MedicationTypeValue = (typeof MEDICATION_TYPE_VALUES)[number];

export const MEDICATION_DOSE_UNIT_VALUES = [
    'milligram',
    'gram',
    'milliliter',
    'piece',
    'drop',
    'injection',
    'unit',
    'sachet',
    'other',
] as const;

export type MedicationDoseUnitValue = (typeof MEDICATION_DOSE_UNIT_VALUES)[number];

export const MEDICATION_MEAL_TIMING_VALUES = [
    'before_food',
    'after_food',
    'with_food',
    'unrelated',
] as const;

export type MedicationMealTimingValue = (typeof MEDICATION_MEAL_TIMING_VALUES)[number];

export type MedicationIntakeFrequencyEveryNDaysValue = `every_${
    | 2
    | 3
    | 4
    | 5
    | 6
    | 7
    | 8
    | 9
    | 10
    | 11
    | 12
    | 13
    | 14
    | 15
    | 16
    | 17
    | 18
    | 19
    | 20
    | 21
    | 22
    | 23
    | 24
    | 25
    | 26
    | 27
    | 28
    | 29
    | 30
    | 31
    | 32
    | 33
    | 34
    | 35
    | 36
    | 37
    | 38
    | 39
    | 40
    | 41
    | 42
    | 43
    | 44
    | 45
    | 46
    | 47
    | 48
    | 49
    | 50
    | 51
    | 52
    | 53
    | 54
    | 55
    | 56
    | 57
    | 58
    | 59
    | 60
}_days`;

export const MEDICATION_INTAKE_FREQUENCY_VALUES = [
    'daily',
    ...(Array.from({ length: 59 }, (_, index) => `every_${index + 2}_days`) as readonly MedicationIntakeFrequencyEveryNDaysValue[]),
    'weekdays',
] as const;

export type MedicationIntakeFrequencyValue = (typeof MEDICATION_INTAKE_FREQUENCY_VALUES)[number];

export type MedicationScheduleListItem = {
    id: number;
    medication_id: number;
    meal_timing: MedicationMealTimingValue;
    intake_frequency: MedicationIntakeFrequencyValue;
    intake_weekdays: number[] | null;
    times_per_day: string;
    dose_quantity: string;
    dose_time: string;
    start_date: string | null;
    end_date: string | null;
};

export type MedicationStockListItem = {
    id: number;
    medication_id: number;
    current_stock: string;
    low_stock: string;
};

export type MedicationSupplyEstimateQuality = 'approx' | 'unknown';

export type MedicationListItem = {
    id: number;
    patient_id: number;
    family_id: number | null;
    name: string;
    dose: string | null;
    dose_unit: MedicationDoseUnitValue | null;
    type_medication: MedicationTypeValue;
    strength: string | null;
    note: string | null;
    schedules: MedicationScheduleListItem[];
    stocks: MedicationStockListItem[];
    supply_estimate_days: number | null;
    supply_estimate_quality: MedicationSupplyEstimateQuality;
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
