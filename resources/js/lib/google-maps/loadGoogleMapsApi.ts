import { importLibrary, setOptions } from '@googlemaps/js-api-loader';

let isConfigured = false;
let placesLibraryPromise: Promise<google.maps.PlacesLibrary> | null = null;

export function getGoogleMapsApiKey(): string | null {
    const key = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

    return typeof key === 'string' && key.trim() !== '' ? key.trim() : null;
}

export function ensureGoogleMapsConfigured(): void {
    const apiKey = getGoogleMapsApiKey();

    if (apiKey === null) {
        throw new Error('VITE_GOOGLE_MAPS_API_KEY is not configured.');
    }

    if (!isConfigured) {
        setOptions({
            key: apiKey,
            v: 'weekly',
        });
        isConfigured = true;
    }
}

export function loadGoogleMapsApi(): Promise<void> {
    ensureGoogleMapsConfigured();

    placesLibraryPromise ??= importLibrary('places');

    return placesLibraryPromise.then(() => undefined);
}

export async function importGoogleMapsPlacesLibrary(): Promise<google.maps.PlacesLibrary> {
    ensureGoogleMapsConfigured();

    placesLibraryPromise ??= importLibrary('places');

    return placesLibraryPromise;
}
