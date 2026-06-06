import { describe, expect, it, vi } from 'vitest';
import type { ComposerTranslation } from 'vue-i18n';
import { medicationCardHeaderSummary } from '@/lib/patient/medications/display/medicationIntakeSlotDisplay';

const t = vi.fn((key: string, params?: Record<string, unknown>) => {
    if (key === 'patient.medications.cardHeaderIntakeTimesCount') {
        return `${params?.count} keer`;
    }

    if (key === 'patient.medications.types.pill') {
        return 'Tablet / pil';
    }

    return key;
}) as unknown as ComposerTranslation;

describe('medicationCardHeaderSummary', () => {
    it('shows intake time count instead of listing times', () => {
        expect(
            medicationCardHeaderSummary(t, {
                dose: null,
                dose_unit: null,
                note: null,
                type_medication: 'liquid',
                doseTimes: ['08:00', '14:00', '20:00'],
            }),
        ).toBe('3 keer');
    });

    it('shows singular intake time count for one scheduled time', () => {
        expect(
            medicationCardHeaderSummary(t, {
                dose: null,
                dose_unit: null,
                note: null,
                type_medication: 'pill',
                doseTimes: ['21:33'],
            }),
        ).toBe('1 keer');
    });
});
