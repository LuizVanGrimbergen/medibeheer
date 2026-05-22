export type FamilyAcceptedTransportAppointment = {
    id: number;
    patient_id: number;
    patient_name: string;
    switch_url: string;
    appointments_url: string;
    doctor_type: string;
    provider_name: string;
    street: string;
    house_number: string;
    postal_code: string;
    city: string;
    starts_at: string;
};

import type { FamilyLowStockPatient } from '@/lib/family/overview/familyLowStockPatients';
import type { FamilyPendingTransportAppointment } from '@/lib/family/overview/familyPendingTransportAppointments';

export type FamilyOverviewScreenProps = {
    low_stock_patients: FamilyLowStockPatient[];
    pending_transport_appointments: FamilyPendingTransportAppointment[];
    accepted_transport_appointments: FamilyAcceptedTransportAppointment[];
};
