import { cn } from '@/lib/utils';

export const mobileShellFormLabelClass =
    'mb-2 block text-lg font-semibold leading-snug text-text-heading';

export const mobileShellFormFieldInputClass =
    'h-auto min-h-14 w-full rounded-2xl border-2 border-border bg-surface px-4 py-3.5 text-lg leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25';

/** Host class for Google `gmp-place-autocomplete` (styled in patient-place-autocomplete.css). */
export const mobileShellFormPlaceAutocompleteClass = 'patient-place-autocomplete';

export const mobileShellFormNativeDateTimeInputClass = cn(
    mobileShellFormFieldInputClass,
    'native-picker-input block min-h-16 py-4 text-xl pr-14 touch-manipulation',
);

export const mobileShellFormNativeDateTimePlaceholderClass =
    'text-text-placeholder pointer-events-none absolute inset-y-0 left-4 z-[1] flex items-center text-xl leading-normal';

export const mobileShellFormSelectBaseClass = `${mobileShellFormFieldInputClass} appearance-none bg-[length:1.5rem] bg-[right_1rem_center] bg-no-repeat pr-14 touch-manipulation`;

export const mobileShellFormFieldInvalidClass =
    'border-danger ring-2 ring-danger/25 focus-visible:border-danger focus-visible:ring-danger/30';

export const mobileShellFormLargeTouchFieldClass =
    'min-h-[3.75rem] !py-4 !text-xl !leading-snug touch-manipulation';

export const mobileShellFormSelectChevronStyle = {
    backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23667d94'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E")`,
};
