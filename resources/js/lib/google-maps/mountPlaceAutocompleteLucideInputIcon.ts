import { MapPin } from 'lucide-vue-next';
import { createApp, h } from 'vue';

export type PlaceAutocompleteLucideInputIconHandle = {
    unmount: () => void;
};

/**
 * Places Lucide `MapPin` in the `input-icon` slot so iOS lays out text beside the icon.
 *
 * @see https://developers.google.com/maps/documentation/javascript/reference/places-widget#PlaceAutocompleteElement.input-icon
 */
export function mountPlaceAutocompleteLucideInputIcon(
    element: google.maps.places.PlaceAutocompleteElement,
): PlaceAutocompleteLucideInputIconHandle {
    const slotHost = document.createElement('span');
    slotHost.setAttribute('slot', 'input-icon');
    slotHost.setAttribute('aria-hidden', 'true');
    slotHost.className = 'patient-place-autocomplete-input-icon';

    const app = createApp({
        render: () =>
            h(MapPin, {
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
