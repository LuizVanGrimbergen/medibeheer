import type { Appointment as PatientAppointment, Paginated } from '@/lib/types';

export type PatientAppointmentsScreenProps = {
    appointments: Paginated<PatientAppointment>;
    linked_families: {
        id: number;
        name: string;
    }[];
};
