import { describe, expect, it } from 'vitest';
import {
    citiesMatch,
    collectAppointmentAddressGeocodeFieldErrors,
    postalCodesMatch,
    streetsMatch,
} from '@/lib/patient/appointments/matchAppointmentAddressToGeocode';

describe('postalCodesMatch', () => {
    it('matches identical Belgian postcodes', () => {
        expect(postalCodesMatch('2970', '2970')).toBe(true);
    });

    it('rejects different postcodes', () => {
        expect(postalCodesMatch('2970', '2000')).toBe(false);
    });
});

describe('citiesMatch', () => {
    it('matches equal city names case-insensitively', () => {
        expect(citiesMatch('Antwerpen', 'antwerpen')).toBe(true);
    });

    it('rejects unrelated cities', () => {
        expect(citiesMatch('Antwerpen', 'Gent')).toBe(false);
    });
});

describe('streetsMatch', () => {
    it('matches when the route name is contained in the input', () => {
        expect(streetsMatch('Joossenlei', 'Joossenlei')).toBe(true);
    });

    it('rejects unrelated street names', () => {
        expect(streetsMatch('Onbestaande Straat', 'Meir')).toBe(false);
    });
});

describe('collectAppointmentAddressGeocodeFieldErrors', () => {
    it('flags fields that do not match the geocoded address', () => {
        const errors = collectAppointmentAddressGeocodeFieldErrors(
            {
                street: 'Onbestaande Straat',
                postal_code: '2970',
                city: 'Schilde',
            },
            {
                street: 'Joossenlei',
                house_number: '29',
                postal_code: '2970',
                city: 'Schilde',
            },
        );

        expect(errors.street).toBeDefined();
        expect(errors.postal_code).toBeUndefined();
        expect(errors.city).toBeUndefined();
    });
});
