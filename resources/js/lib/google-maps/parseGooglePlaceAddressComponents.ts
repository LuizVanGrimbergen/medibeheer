export type GooglePlaceAddressComponent = {
    types: string[];
    longText?: string | null;
    shortText?: string | null;
};

export type ParsedAppointmentAddress = {
    street: string;
    house_number: string;
    postal_code: string;
    city: string;
};

function componentText(
    component: GooglePlaceAddressComponent | undefined,
    prefer: 'long' | 'short' = 'long',
): string {
    if (component === undefined) {
        return '';
    }

    const primary =
        prefer === 'long' ? component.longText : component.shortText;
    const fallback =
        prefer === 'long' ? component.shortText : component.longText;

    return (primary ?? fallback ?? '').trim();
}

function findComponent(
    components: GooglePlaceAddressComponent[] | null | undefined,
    type: string,
): GooglePlaceAddressComponent | undefined {
    return components?.find((component) => component.types.includes(type));
}

/**
 * Maps Places API (New) addressComponents to appointment form fields (NL/EU).
 */
export function parseGooglePlaceAddressComponents(
    components: GooglePlaceAddressComponent[] | null | undefined,
): ParsedAppointmentAddress {
    const route = componentText(findComponent(components, 'route'));
    const streetNumber = componentText(
        findComponent(components, 'street_number'),
    );
    const postalCode = componentText(findComponent(components, 'postal_code'));
    const city =
        componentText(findComponent(components, 'locality')) ||
        componentText(findComponent(components, 'postal_town')) ||
        componentText(findComponent(components, 'administrative_area_level_2'));

    let street = route;

    if (street === '') {
        street = componentText(findComponent(components, 'street_address'));
    }

    return {
        street,
        house_number: streetNumber,
        postal_code: postalCode,
        city,
    };
}
