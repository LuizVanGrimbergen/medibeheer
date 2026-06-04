import { describe, expect, it } from 'vitest';
import {
    collectAppointmentAddressFieldErrors,
    isAppointmentAddressProvided,
    isAppointmentAddressValidationRequired,
    isBelgianPostalCodeValid,
} from '@/lib/patient/appointments/appointmentAddressValidation';

describe('isBelgianPostalCodeValid', () => {
    it('accepts four-digit Belgian postcodes', () => {
        expect(isBelgianPostalCodeValid('2970')).toBe(true);
        expect(isBelgianPostalCodeValid(' 2000 ')).toBe(true);
    });

    it('rejects invalid postcodes', () => {
        expect(isBelgianPostalCodeValid('297')).toBe(false);
        expect(isBelgianPostalCodeValid('29700')).toBe(false);
        expect(isBelgianPostalCodeValid('29A0')).toBe(false);
    });
});

describe('isAppointmentAddressValidationRequired', () => {
    it('requires an address when transport is needed', () => {
        expect(
            isAppointmentAddressValidationRequired(false, {
                street: '',
                postal_code: '',
                city: '',
            }),
        ).toBe(false);

        expect(
            isAppointmentAddressValidationRequired(true, {
                street: '',
                postal_code: '',
                city: '',
            }),
        ).toBe(true);
    });

    it('requires validation when any address field is filled', () => {
        expect(
            isAppointmentAddressProvided({
                street: 'Meir',
                postal_code: '',
                city: '',
            }),
        ).toBe(true);

        expect(
            isAppointmentAddressValidationRequired(false, {
                street: 'Meir',
                postal_code: '',
                city: '',
            }),
        ).toBe(true);
    });
});

describe('collectAppointmentAddressFieldErrors', () => {
    it('skips required-field errors when address is optional', () => {
        expect(
            collectAppointmentAddressFieldErrors(
                {
                    street: '',
                    postal_code: '',
                    city: '',
                },
                { required: false },
            ),
        ).toEqual({});
    });
    it('returns no errors for a complete Belgian address', () => {
        expect(
            collectAppointmentAddressFieldErrors({
                street: 'Meir',
                postal_code: '2000',
                city: 'Antwerpen',
            }),
        ).toEqual({});
    });

    it('requires street, postcode and city', () => {
        const errors = collectAppointmentAddressFieldErrors({
            street: '',
            postal_code: '',
            city: '',
        });

        expect(errors.street).toBeDefined();
        expect(errors.postal_code).toBeDefined();
        expect(errors.city).toBeDefined();
    });

    it('rejects postcodes that are not four digits', () => {
        const errors = collectAppointmentAddressFieldErrors({
            street: 'Meir',
            postal_code: '200',
            city: 'Antwerpen',
        });

        expect(errors.postal_code).toBeDefined();
        expect(errors.city).toBeUndefined();
    });
});
