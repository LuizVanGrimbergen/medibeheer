import { describe, expect, it } from 'vitest';
import type { InventoryVacationShareImagePayload } from '@/lib/patient/inventory/inventoryVacationShareImageTypes';
import {
    INVENTORY_VACATION_MEDICATIONS_PER_SHARE_PAGE,
    inventoryVacationPlannedShareImageCount,
    splitInventoryVacationShareImagePayloads,
} from '@/lib/patient/inventory/splitInventoryVacationShareImagePayloads';

function payloadWithItemCount(count: number): InventoryVacationShareImagePayload {
    return {
        title: 'Vakantie',
        periodHeading: 'Periode',
        daysLabel: '7 dagen',
        departureLabel: 'Vertrek',
        departureDate: '1 jul 2026',
        returnLabel: 'Terugkomst',
        returnDate: '8 jul 2026',
        savedPackageHint: null,
        expiringPrescriptions: [],
        totalLabel: 'Totaal',
        listHeading: 'Medicatie',
        emptyMessage: null,
        skippedNote: null,
        footerLabel: 'Medibeheer',
        pageLabel: null,
        items: Array.from({ length: count }, (_, index) => ({
            medicationId: index + 1,
            name: `Medicatie ${index + 1}`,
            primaryLabel: 'Te halen',
            primaryValue: '10 stuks',
            secondaryLabel: 'Nodig voor reis',
            secondaryValue: '20 stuks',
            totalLabel: 'Totaal',
            totalNumeric: '10',
            totalUnitChip: 'stuks',
        })),
    };
}

describe('splitInventoryVacationShareImagePayloads', () => {
    it('uses five medications per share page by default', () => {
        expect(INVENTORY_VACATION_MEDICATIONS_PER_SHARE_PAGE).toBe(5);
    });

    it('plans one image for up to five medications and more above that', () => {
        expect(inventoryVacationPlannedShareImageCount(5)).toBe(1);
        expect(inventoryVacationPlannedShareImageCount(6)).toBe(2);
        expect(inventoryVacationPlannedShareImageCount(12)).toBe(3);
    });

    it('keeps a single page when there are at most five medications', () => {
        const pages = splitInventoryVacationShareImagePayloads(
            payloadWithItemCount(5),
        );

        expect(pages).toHaveLength(1);
        expect(pages[0]?.items).toHaveLength(5);
        expect(pages[0]?.pageLabel).toBeNull();
    });

    it('splits into stepped pages with five medications each', () => {
        const pages = splitInventoryVacationShareImagePayloads(
            payloadWithItemCount(12),
        );

        expect(pages).toHaveLength(3);
        expect(pages.map((page) => page.items.length)).toEqual([5, 5, 2]);
        expect(pages.map((page) => page.pageLabel)).toEqual([
            '1 / 3',
            '2 / 3',
            '3 / 3',
        ]);
    });
});
