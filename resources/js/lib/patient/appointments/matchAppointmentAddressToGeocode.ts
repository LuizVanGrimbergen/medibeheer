import type { ParsedAppointmentAddress } from '@/lib/google-maps/parseGooglePlaceAddressComponents';
import type {
    AppointmentAddressFieldErrors,
    AppointmentAddressFieldValues,
} from '@/lib/patient/appointments/appointmentAddressValidation';
import { isBelgianPostalCodeValid } from '@/lib/patient/appointments/appointmentAddressValidation';
import appointmentsNl from '@/translations/nl/patient/appointments';

function normalizeAddressToken(value: string): string {
    return value
        .trim()
        .toLowerCase()
        .normalize('NFD')
        .replace(/\p{M}/gu, '')
        .replace(/\s+/g, ' ');
}

function extractBelgianPostalCode(value: string): string {
    const match = value.trim().match(/\b(\d{4})\b/);

    return match?.[1] ?? value.trim();
}

export function postalCodesMatch(
    inputPostalCode: string,
    geocodedPostalCode: string,
): boolean {
    if (!isBelgianPostalCodeValid(inputPostalCode)) {
        return false;
    }

    const input = extractBelgianPostalCode(inputPostalCode);
    const geocoded = extractBelgianPostalCode(geocodedPostalCode);

    return input.length === 4 && input === geocoded;
}

export function citiesMatch(inputCity: string, geocodedCity: string): boolean {
    const input = normalizeAddressToken(inputCity);
    const geocoded = normalizeAddressToken(geocodedCity);

    if (input === '' || geocoded === '') {
        return false;
    }

    return (
        input === geocoded ||
        input.includes(geocoded) ||
        geocoded.includes(input)
    );
}

export function streetsMatch(
    inputStreet: string,
    geocodedStreet: string,
): boolean {
    const input = normalizeAddressToken(inputStreet);
    const geocoded = normalizeAddressToken(geocodedStreet);

    if (input === '' || geocoded === '') {
        return false;
    }

    return (
        input === geocoded ||
        input.includes(geocoded) ||
        geocoded.includes(input)
    );
}

export function collectAppointmentAddressGeocodeFieldErrors(
    input: AppointmentAddressFieldValues,
    geocoded: ParsedAppointmentAddress,
): AppointmentAddressFieldErrors {
    const mismatchMessage = appointmentsNl.validation.addressComponentMismatch;
    const out: AppointmentAddressFieldErrors = {};

    if (!streetsMatch(input.street, geocoded.street)) {
        out.street = mismatchMessage;
    }

    if (!postalCodesMatch(input.postal_code, geocoded.postal_code)) {
        out.postal_code = mismatchMessage;
    }

    if (!citiesMatch(input.city, geocoded.city)) {
        out.city = mismatchMessage;
    }

    return out;
}
