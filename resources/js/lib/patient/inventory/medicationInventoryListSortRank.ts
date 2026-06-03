import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import type { MedicationListItem } from '@/lib/types';

/** Lower rank sorts first: 0 critical, 1 warning, 2 safe, 3 missing tone (e.g. invalid stock). */
export function medicationInventoryListSortRank(
    medication: MedicationListItem,
): number {
    const tone = medicationListVisualTone(medication);

    if (tone === 'critical') {
        return 0;
    }

    if (tone === 'warning') {
        return 1;
    }

    if (tone === 'safe') {
        return 2;
    }

    return 3;
}

function hasApproxSupplyDays(medication: MedicationListItem): boolean {
    return (
        medication.supply_estimate_quality === 'approx' &&
        medication.supply_estimate_days !== null
    );
}

/** Sort by remaining supply days (ascending), then visual tone, then name. Unknown estimates last. */
export function compareMedicationInventoryListItems(
    a: MedicationListItem,
    b: MedicationListItem,
): number {
    const aHas = hasApproxSupplyDays(a);
    const bHas = hasApproxSupplyDays(b);

    if (aHas && bHas) {
        const dayA = a.supply_estimate_days!;
        const dayB = b.supply_estimate_days!;

        if (dayA !== dayB) {
            return dayA - dayB;
        }

        const rankDiff =
            medicationInventoryListSortRank(a) -
            medicationInventoryListSortRank(b);

        if (rankDiff !== 0) {
            return rankDiff;
        }

        return a.name.localeCompare(b.name, 'nl', { sensitivity: 'base' });
    }

    if (aHas && !bHas) {
        return -1;
    }

    if (!aHas && bHas) {
        return 1;
    }

    const rankDiff =
        medicationInventoryListSortRank(a) - medicationInventoryListSortRank(b);

    if (rankDiff !== 0) {
        return rankDiff;
    }

    return a.name.localeCompare(b.name, 'nl', { sensitivity: 'base' });
}
