import { describe, expect, it } from 'vitest';
import {
    parseGooglePlaceAddressComponents,
    type GooglePlaceAddressComponent,
} from '@/lib/google-maps/parseGooglePlaceAddressComponents';

function component(
    type: string,
    longText: string,
): GooglePlaceAddressComponent {
    return { types: [type], longText, shortText: longText };
}

describe('parseGooglePlaceAddressComponents', () => {
    it('maps route, street number, postal code and locality', () => {
        const parsed = parseGooglePlaceAddressComponents([
            component('route', 'Joossenlei'),
            component('street_number', '29'),
            component('postal_code', '2970'),
            component('locality', 'Schilde'),
        ]);

        expect(parsed).toEqual({
            street: 'Joossenlei',
            house_number: '29',
            postal_code: '2970',
            city: 'Schilde',
        });
    });

    it('prefers postal_town when locality is missing', () => {
        const parsed = parseGooglePlaceAddressComponents([
            component('route', 'Grote Markt'),
            component('postal_code', '2000'),
            component('postal_town', 'Antwerpen'),
        ]);

        expect(parsed.city).toBe('Antwerpen');
        expect(parsed.street).toBe('Grote Markt');
    });
});
