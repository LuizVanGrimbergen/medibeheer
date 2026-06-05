import { describe, expect, it } from 'vitest';
import {
    prescriptionShowsCollapsedPickupAction,
    prescriptionShowsExpandedPickupControl,
} from '@/lib/patient/prescriptions/prescriptionCollapsedPickupVisibility';

describe('prescriptionShowsCollapsedPickupAction', () => {
    it('shows the pickup action when collapsed', () => {
        expect(prescriptionShowsCollapsedPickupAction(false)).toBe(true);
    });

    it('hides the pickup action when expanded', () => {
        expect(prescriptionShowsCollapsedPickupAction(true)).toBe(false);
    });
});

describe('prescriptionShowsExpandedPickupControl', () => {
    it('shows pickup in expanded details when open', () => {
        expect(prescriptionShowsExpandedPickupControl(true)).toBe(true);
    });

    it('hides pickup in expanded details when collapsed', () => {
        expect(prescriptionShowsExpandedPickupControl(false)).toBe(false);
    });
});
