import type { Ref } from 'vue';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/form/AppointmentFormTypes';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';
import { focusPlaceAutocompleteSearch } from '@/lib/google-maps/focusPlaceAutocompleteSearch';
import {
    attachPlaceAutocompleteMobileInputBehavior,
    shouldAutoFocusPlaceAutocomplete,
} from '@/lib/google-maps/placeAutocompleteInput';
import {
    getGoogleMapsApiKey,
    importGoogleMapsPlacesLibrary,
} from '@/lib/google-maps/loadGoogleMapsApi';
import { mountPlaceAutocompleteLucideClearIcon } from '@/lib/google-maps/mountPlaceAutocompleteLucideClearIcon';
import { mountPlaceAutocompleteLucideInputIcon } from '@/lib/google-maps/mountPlaceAutocompleteLucideInputIcon';
import { parseGooglePlaceAddressComponents } from '@/lib/google-maps/parseGooglePlaceAddressComponents';
import { looseInertiaForm } from '@/lib/inertia/looseInertiaForm';
import { isAppointmentAddressComplete } from '@/lib/patient/appointments/isAppointmentAddressComplete';
import { mobileShellFormPlaceAutocompleteClass } from '@/lib/shell/mobileShellFormFieldClasses';

function scheduleFocusPlaceAutocompleteSearch(host: HTMLElement | null): void {
    if (focusPlaceAutocompleteSearch(host)) {
        return;
    }

    window.setTimeout(() => {
        focusPlaceAutocompleteSearch(host);
    }, 50);
}

export function useAppointmentAddressPlaceAutocomplete(options: {
    form: AppointmentFormWithErrors;
    hostRef: Ref<HTMLElement | null>;
    placeholder: string;
    onUnavailable?: () => void;
    onPlaceSelected?: () => void;
}): {
    isAvailable: Ref<boolean>;
    loadError: Ref<string | null>;
    placesVerifiedSnapshot: Ref<string | null>;
} {
    const isAvailable = ref(getGoogleMapsApiKey() !== null);
    const loadError = ref<string | null>(null);
    const placesVerifiedSnapshot = ref<string | null>(null);

    let autocompleteElement: google.maps.places.PlaceAutocompleteElement | null =
        null;
    let selectListener: ((event: Event) => void) | null = null;
    let clearIconHandle: ReturnType<
        typeof mountPlaceAutocompleteLucideClearIcon
    > | null = null;
    let inputIconHandle: ReturnType<
        typeof mountPlaceAutocompleteLucideInputIcon
    > | null = null;
    let detachMobileInputBehavior: (() => void) | null = null;

    const syncSearchInputValue = (): void => {
        if (autocompleteElement === null) {
            return;
        }

        if (!isAppointmentAddressComplete(options.form)) {
            autocompleteElement.value = '';

            return;
        }

        autocompleteElement.value = formatAppointmentAddress(options.form);
    };

    watch(
        () =>
            [
                options.form.street,
                options.form.house_number,
                options.form.postal_code,
                options.form.city,
            ] as const,
        () => {
            syncSearchInputValue();
        },
    );

    onMounted(() => {
        if (!isAvailable.value || options.hostRef.value === null) {
            options.onUnavailable?.();

            return;
        }

        void (async () => {
            try {
                const { PlaceAutocompleteElement } =
                    await importGoogleMapsPlacesLibrary();
                const host = options.hostRef.value;

                if (host === null) {
                    return;
                }

                const element = new PlaceAutocompleteElement({
                    includedRegionCodes: ['be'],
                });

                element.className = `${mobileShellFormPlaceAutocompleteClass} w-full`;
                element.setAttribute('no-input-icon', '');
                element.requestedLanguage = 'nl';
                element.requestedRegion = 'be';
                element.placeholder = options.placeholder;

                const applyPlaceSelection = async (
                    event: google.maps.places.PlacePredictionSelectEvent,
                ): Promise<void> => {
                    const placePrediction = event.placePrediction;

                    if (placePrediction === undefined) {
                        return;
                    }

                    const place = placePrediction.toPlace();

                    await place.fetchFields({
                        fields: ['addressComponents'],
                    });

                    const parsed = parseGooglePlaceAddressComponents(
                        place.addressComponents ?? undefined,
                    );

                    options.form.street = parsed.street;
                    options.form.house_number = parsed.house_number;
                    options.form.postal_code = parsed.postal_code;
                    options.form.city = parsed.city;

                    looseInertiaForm(options.form).clearErrors(
                        'street',
                        'house_number',
                        'postal_code',
                        'city',
                    );

                    syncSearchInputValue();
                    placesVerifiedSnapshot.value = formatAppointmentAddress(
                        options.form,
                    );
                    options.onPlaceSelected?.();
                };

                selectListener = (event: Event) => {
                    void applyPlaceSelection(
                        event as google.maps.places.PlacePredictionSelectEvent,
                    );
                };

                element.addEventListener('gmp-select', selectListener);
                inputIconHandle =
                    mountPlaceAutocompleteLucideInputIcon(element);
                clearIconHandle =
                    mountPlaceAutocompleteLucideClearIcon(element);
                host.replaceChildren(element);
                autocompleteElement = element;
                syncSearchInputValue();
                detachMobileInputBehavior =
                    attachPlaceAutocompleteMobileInputBehavior(host);

                if (shouldAutoFocusPlaceAutocomplete()) {
                    scheduleFocusPlaceAutocompleteSearch(host);
                }
            } catch (error) {
                isAvailable.value = false;
                loadError.value =
                    error instanceof Error
                        ? error.message
                        : 'Google Places could not load.';
                options.onUnavailable?.();
            }
        })();
    });

    onBeforeUnmount(() => {
        if (autocompleteElement !== null && selectListener !== null) {
            autocompleteElement.removeEventListener(
                'gmp-select',
                selectListener,
            );
        }

        detachMobileInputBehavior?.();
        clearIconHandle?.unmount();
        inputIconHandle?.unmount();
        options.hostRef.value?.replaceChildren();
        autocompleteElement = null;
        selectListener = null;
        clearIconHandle = null;
        inputIconHandle = null;
        detachMobileInputBehavior = null;
    });

    return { isAvailable, loadError, placesVerifiedSnapshot };
}
