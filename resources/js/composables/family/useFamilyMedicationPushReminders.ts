import { useMedicationPushReminders } from '@/composables/useMedicationPushReminders';

export function useFamilyMedicationPushReminders() {
    return useMedicationPushReminders('family_member');
}
