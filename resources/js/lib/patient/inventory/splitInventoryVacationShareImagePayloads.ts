import type { InventoryVacationShareImagePayload } from '@/lib/patient/inventory/inventoryVacationShareImageTypes';

/** Keeps each saved image readable on a phone screen. */
export const INVENTORY_VACATION_MEDICATIONS_PER_SHARE_PAGE = 4;

export function splitInventoryVacationShareImagePayloads(
    payload: InventoryVacationShareImagePayload,
    itemsPerPage: number = INVENTORY_VACATION_MEDICATIONS_PER_SHARE_PAGE,
): InventoryVacationShareImagePayload[] {
    if (payload.items.length === 0) {
        return [
            {
                ...payload,
                pageLabel: null,
            },
        ];
    }

    if (payload.items.length <= itemsPerPage) {
        return [
            {
                ...payload,
                pageLabel: null,
            },
        ];
    }

    const pages: InventoryVacationShareImagePayload[] = [];
    const totalPages = Math.ceil(payload.items.length / itemsPerPage);

    for (let pageIndex = 0; pageIndex < totalPages; pageIndex += 1) {
        const start = pageIndex * itemsPerPage;
        const pageItems = payload.items.slice(start, start + itemsPerPage);
        const isLastPage = pageIndex === totalPages - 1;

        pages.push({
            ...payload,
            items: pageItems,
            pageLabel: `${pageIndex + 1} / ${totalPages}`,
            skippedNote: isLastPage ? payload.skippedNote : null,
            emptyMessage: null,
        });
    }

    return pages;
}
