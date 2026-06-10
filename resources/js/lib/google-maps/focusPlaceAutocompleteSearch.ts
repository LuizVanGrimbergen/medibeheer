import {
    configurePlaceAutocompleteInput,
    getPlaceAutocompleteInput,
    scrollPlaceAutocompleteHostIntoScrollParent,
    shouldAutoFocusPlaceAutocomplete,
} from '@/lib/google-maps/placeAutocompleteInput';

export function focusPlaceAutocompleteSearch(
    host: HTMLElement | null,
): boolean {
    if (host === null || !shouldAutoFocusPlaceAutocomplete()) {
        return false;
    }

    const input = getPlaceAutocompleteInput(host);

    if (input === null) {
        return false;
    }

    configurePlaceAutocompleteInput(input);
    input.focus({ preventScroll: true });
    scrollPlaceAutocompleteHostIntoScrollParent(host);

    return true;
}
