import { describe, expect, it } from 'vitest';
import { isAppointmentAddressComplete } from '@/lib/patient/appointments/isAppointmentAddressComplete';

describe('isAppointmentAddressComplete', () => {
    it('returns true when street, postal code and city are filled', () => {
        expect(
            isAppointmentAddressComplete({
                street: 'Meir',
                postal_code: '2000',
                city: 'Antwerpen',
            }),
        ).toBe(true);
    });

    it('returns false when a required field is empty', () => {
        expect(
            isAppointmentAddressComplete({
                street: 'Meir',
                postal_code: '',
                city: 'Antwerpen',
            }),
        ).toBe(false);
    });
});
