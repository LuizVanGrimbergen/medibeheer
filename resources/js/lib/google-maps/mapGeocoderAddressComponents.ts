import type { GooglePlaceAddressComponent } from '@/lib/google-maps/parseGooglePlaceAddressComponents';

export function mapGeocoderAddressComponents(
    components: google.maps.GeocoderAddressComponent[] | null | undefined,
): GooglePlaceAddressComponent[] {
    return (components ?? []).map((component) => ({
        types: component.types,
        longText: component.long_name,
        shortText: component.short_name,
    }));
}
