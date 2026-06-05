import { router } from '@inertiajs/vue3';

export function reloadFamilyUpdatesPage(): void {
    router.reload({
        only: ['updates_checkins', 'updates_medication_intakes', 'family'],
    });
}
