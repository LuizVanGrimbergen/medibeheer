export function getPlaceAutocompleteInput(
    host: HTMLElement | null,
): HTMLInputElement | null {
    const widget = host?.querySelector('gmp-place-autocomplete');
    const input = widget?.shadowRoot?.querySelector('input');

    return input instanceof HTMLInputElement ? input : null;
}

export function shouldAutoFocusPlaceAutocomplete(): boolean {
    if (globalThis.window === undefined) {
        return false;
    }

    if (globalThis.matchMedia('(pointer: coarse)').matches) {
        return false;
    }

    if (globalThis.matchMedia('(max-width: 639px)').matches) {
        return false;
    }

    return true;
}

export function configurePlaceAutocompleteInput(
    input: HTMLInputElement,
): void {
    input.autocomplete = 'off';
    input.autocapitalize = 'off';
    input.spellcheck = false;
    input.setAttribute('enterkeyhint', 'search');
    input.setAttribute('inputmode', 'search');
}

export function scrollPlaceAutocompleteHostIntoScrollParent(
    host: HTMLElement,
): void {
    const scrollParent = host.closest('[class*="overflow-y-auto"]');

    if (!(scrollParent instanceof HTMLElement)) {
        return;
    }

    const hostRect = host.getBoundingClientRect();
    const parentRect = scrollParent.getBoundingClientRect();
    const offset = hostRect.top - parentRect.top - 16;

    scrollParent.scrollTop += offset;
}

export function attachPlaceAutocompleteMobileInputBehavior(
    host: HTMLElement,
): () => void {
    const input = getPlaceAutocompleteInput(host);

    if (input === null) {
        return () => {};
    }

    configurePlaceAutocompleteInput(input);

    const onFocus = (): void => {
        globalThis.setTimeout(() => {
            scrollPlaceAutocompleteHostIntoScrollParent(host);
        }, 300);
    };

    input.addEventListener('focus', onFocus);

    return () => {
        input.removeEventListener('focus', onFocus);
    };
}
