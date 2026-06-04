export function focusPlaceAutocompleteSearch(
    host: HTMLElement | null,
): boolean {
    const widget = host?.querySelector('gmp-place-autocomplete');

    if (widget === null || widget === undefined) {
        return false;
    }

    const input = widget.shadowRoot?.querySelector('input');

    if (!(input instanceof HTMLInputElement)) {
        return false;
    }

    input.focus({ preventScroll: true });

    return true;
}
