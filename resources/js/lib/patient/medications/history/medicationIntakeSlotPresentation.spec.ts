import { describe, expect, it } from 'vitest';
import {
    isMedicationIntakeTakenWithinWindow,
    resolveMedicationIntakeSlotStatus,
} from '@/lib/patient/medications/history/medicationIntakeSlotPresentation';

describe('medicationIntakeSlotPresentation', () => {
    it('marks untaken slots as not taken', () => {
        expect(
            resolveMedicationIntakeSlotStatus({
                dose_time: '08:00',
                snooze_minutes: 60,
                taken_at: null,
            }),
        ).toBe('not_taken');
    });

    it('marks on-time intakes as taken', () => {
        const slot = {
            dose_time: '08:00',
            snooze_minutes: 60,
            taken_at: '2026-06-05T08:30:00+02:00',
        };

        expect(isMedicationIntakeTakenWithinWindow(slot)).toBe(true);
        expect(resolveMedicationIntakeSlotStatus(slot)).toBe('taken');
    });

    it('marks intakes after the snooze window as late', () => {
        const slot = {
            dose_time: '08:00',
            snooze_minutes: 60,
            taken_at: '2026-06-05T09:15:00+02:00',
        };

        expect(isMedicationIntakeTakenWithinWindow(slot)).toBe(false);
        expect(resolveMedicationIntakeSlotStatus(slot)).toBe('late');
    });
});
