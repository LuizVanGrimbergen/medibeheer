import { X } from 'lucide-vue-next';
import { createApp, h } from 'vue';

export type PlaceAutocompleteLucideClearIconHandle = {
    unmount: () => void;
};

/**
 * Replaces Google's default clear icon with Lucide `X` via the `clear-icon` slot.
 *
 * @see https://developers.google.com/maps/documentation/javascript/reference/places-widget#PlaceAutocompleteElement.clear-icon
 */
export function mountPlaceAutocompleteLucideClearIcon(
    element: google.maps.places.PlaceAutocompleteElement,
): PlaceAutocompleteLucideClearIconHandle {
    const slotHost = document.createElement('span');
    slotHost.setAttribute('slot', 'clear-icon');
    slotHost.setAttribute('aria-hidden', 'true');
    slotHost.className = 'patient-place-autocomplete-clear-icon';

    const app = createApp({
        render: () =>
            h(X, {
                size: 20,
                strokeWidth: 2,
            }),
    });

    app.mount(slotHost);
    element.appendChild(slotHost);

    return {
        unmount: () => {
            app.unmount();
            slotHost.remove();
        },
    };
}
