import { useMedicationPushReminders } from '@/composables/useMedicationPushReminders';

export function usePatientMedicationPushReminders() {
    return useMedicationPushReminders('patient');
}
