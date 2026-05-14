import { medicationStockProgressTone } from '@/lib/patient/inventory/medicationStockProgressTone';
import type { MedicationListItem } from '@/lib/types';

/** Lower rank sorts first: 0 critical, 1 warning, 2 safe, 3 missing stock or non-numeric values. */
export function medicationInventoryListSortRank(medication: MedicationListItem): number {
    const stock = medication.stocks[0];

    if (stock === undefined) {
        return 3;
    }

    const tone = medicationStockProgressTone(stock.current_stock, stock.low_stock);

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
