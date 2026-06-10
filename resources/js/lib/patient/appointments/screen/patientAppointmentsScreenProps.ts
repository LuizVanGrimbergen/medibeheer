import type { Paginated, Appointment as PatientAppointment } from '@/lib/types';

export type PatientAppointmentView = 'planned';

export type PatientAppointmentsScreenProps = {
    appointments?: Paginated<PatientAppointment>;
    linked_families?: {
        id: number;
        name: string;
    }[];
    appointment_view: PatientAppointmentView;
    appointment_tab_totals: { planned: number; completed: number };
    open_create_dialog?: boolean;
};
