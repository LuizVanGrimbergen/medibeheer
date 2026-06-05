import type { InertiaForm } from '@inertiajs/vue3';

export type PatientPrescriptionFormData = {
    quantity: number | null;
    prescription_expiry_dates: string[];
};

export type PatientPrescriptionForm = InertiaForm<PatientPrescriptionFormData>;
