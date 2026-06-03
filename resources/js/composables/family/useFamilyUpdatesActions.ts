import { router } from '@inertiajs/vue3';
import type { FamilyUpdatesPeriodDays } from '@/lib/family/updates/familyUpdatesScreenProps';

function setUpdatesPeriodDays(
    next: FamilyUpdatesPeriodDays,
    current: FamilyUpdatesPeriodDays,
): void {
    if (next === current) {
        return;
    }

    router.get(
        route('family.updates', { period_days: next }),
        {},
        { preserveState: true, preserveScroll: true },
    );
}

export function setUpdatesPeriodDaysFromToggle(
    next: string,
    current: FamilyUpdatesPeriodDays,
): void {
    if (next !== '1' && next !== '3' && next !== '5') {
        return;
    }

    setUpdatesPeriodDays(Number(next) as FamilyUpdatesPeriodDays, current);
}

export function reloadFamilyUpdatesPage(): void {
    router.reload({
        only: [
            'updates_checkins',
            'updates_medication_intakes',
            'updates_period_days',
            'family',
        ],
    });
}
