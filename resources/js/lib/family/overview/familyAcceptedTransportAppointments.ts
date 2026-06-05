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

import type { FamilyExpiringPrescriptionPatient } from '@/lib/family/overview/familyExpiringPrescriptionPatients';
import type { FamilyLowStockPatient } from '@/lib/family/overview/familyLowStockPatients';
import type { FamilyPendingTransportAppointment } from '@/lib/family/overview/familyPendingTransportAppointments';
import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import type { DailyCheckin } from '@/lib/types';

export type FamilyOverviewScreenProps = {
    low_stock_patients: FamilyLowStockPatient[];
    expiring_prescription_patients: FamilyExpiringPrescriptionPatient[];
    updates_checkins: DailyCheckin[];
    updates_medication_intakes: MedicationIntakeHistorySlot[];
    pending_transport_appointments: FamilyPendingTransportAppointment[];
    accepted_transport_appointments: FamilyAcceptedTransportAppointment[];
};
