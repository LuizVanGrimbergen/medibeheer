import type { Appointment } from '@/lib/types';

export type PatientAppointmentOutcomePageProps = {
    appointment: Appointment;
    show_schedule_next_prompt?: boolean;
};
