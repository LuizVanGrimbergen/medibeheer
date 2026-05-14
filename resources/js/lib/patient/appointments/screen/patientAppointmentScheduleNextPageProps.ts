export type PatientAppointmentScheduleNextOutcome = 'done' | 'cancelled';

export type PatientAppointmentScheduleNextPageProps = {
    outcome: PatientAppointmentScheduleNextOutcome;
};
