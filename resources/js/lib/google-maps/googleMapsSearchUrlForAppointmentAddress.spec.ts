import { describe, expect, it } from 'vitest';
import {
    googleMapsDirectionsUrlForAppointmentAddress,
    googleMapsSearchUrlForAppointmentAddress,
} from '@/lib/google-maps/googleMapsSearchUrlForAppointmentAddress';

describe('googleMapsSearchUrlForAppointmentAddress', () => {
    it('builds a Google Maps search URL for a formatted appointment address', () => {
        const url = googleMapsSearchUrlForAppointmentAddress({
            street: 'Fazantenlaan',
            house_number: '19',
            postal_code: '2970',
            city: 'Schilde',
        });

        expect(url).toBe(
            'https://www.google.com/maps/search/?api=1&query=Fazantenlaan%2019%2C%202970%20Schilde',
        );
    });

    it('returns null when the address is empty', () => {
        expect(
            googleMapsSearchUrlForAppointmentAddress({
                street: '',
                house_number: '',
                postal_code: '',
                city: '',
            }),
        ).toBeNull();
    });

    it('builds a Google Maps directions URL for a formatted appointment address', () => {
        const url = googleMapsDirectionsUrlForAppointmentAddress({
            street: 'Fazantenlaan',
            house_number: '19',
            postal_code: '2970',
            city: 'Schilde',
        });

        expect(url).toBe(
            'https://www.google.com/maps/dir/?api=1&destination=Fazantenlaan%2019%2C%202970%20Schilde',
        );
    });
});
