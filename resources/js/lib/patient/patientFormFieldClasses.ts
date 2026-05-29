import { cn } from '@/lib/utils';

export const patientFormLabelClass =
    'mb-2 block text-lg font-semibold leading-snug text-text-heading';

export const patientFormFieldInputClass =
    'h-auto min-h-14 w-full rounded-2xl border-2 border-border bg-surface px-4 py-3.5 text-lg leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25';

export const patientFormNativeDateTimeInputClass = cn(
    patientFormFieldInputClass,
    'native-picker-input block min-h-16 py-4 text-xl pr-14 touch-manipulation',
);

export const patientFormSelectBaseClass = `${patientFormFieldInputClass} appearance-none bg-[length:1.5rem] bg-[right_1rem_center] bg-no-repeat pr-14 touch-manipulation`;

export const patientFormFieldInvalidClass =
    'border-danger ring-2 ring-danger/25 focus-visible:border-danger focus-visible:ring-danger/30';

export const patientFormLargeTouchFieldClass =
    'min-h-[3.75rem] !py-4 !text-xl !leading-snug touch-manipulation';

export const patientFormSelectChevronStyle = {
    backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23667d94'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E")`,
};
