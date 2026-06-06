import { describe, expect, it } from 'vitest';
import {
    prescriptionShowsExpandedPickupControl,
    prescriptionShowsPrimaryPickupAction,
} from '@/lib/patient/prescriptions/prescriptionCollapsedPickupVisibility';

describe('prescriptionShowsPrimaryPickupAction', () => {
    it('always shows the primary pickup action above the details toggle', () => {
        expect(prescriptionShowsPrimaryPickupAction()).toBe(true);
    });
});

describe('prescriptionShowsExpandedPickupControl', () => {
    it('never duplicates pickup inside expanded details', () => {
        expect(prescriptionShowsExpandedPickupControl()).toBe(false);
    });
});
