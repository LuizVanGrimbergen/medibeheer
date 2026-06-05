import { scrollExpandedSectionIntoView } from '@/lib/ui/scrollExpandedSectionIntoView';

export function scrollPatientListCardDetailsIntoView(
    element: HTMLElement | null | undefined,
): void {
    scrollExpandedSectionIntoView(element);
}
