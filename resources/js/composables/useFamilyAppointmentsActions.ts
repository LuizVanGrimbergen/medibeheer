import { router } from '@inertiajs/vue3';
import type { FamilyAppointmentView } from '@/lib/family/appointments/familyAppointmentsScreenProps';

function setAppointmentView(
    next: FamilyAppointmentView,
    current: FamilyAppointmentView,
): void {
    if (next === current) {
        return;
    }

    router.get(
        route('family.appointments', { view: next, page: 1 }),
        {},
        { preserveState: true, preserveScroll: true },
    );
}

export function setAppointmentViewFromToggle(
    next: string,
    current: FamilyAppointmentView,
): void {
    if (next !== 'planned' && next !== 'completed') {
        return;
    }

    setAppointmentView(next, current);
}

export function acceptTransport(acceptUrl: string): void {
    router.post(acceptUrl, {}, { preserveScroll: true });
}

export function declineTransport(declineUrl: string | null): void {
    if (declineUrl === null) {
        return;
    }

    router.post(declineUrl, {}, { preserveScroll: true });
}

