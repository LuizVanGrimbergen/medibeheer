import { INVENTORY_VACATION_SHARE_IMAGE_EXTENSION } from '@/lib/patient/inventory/shareInventoryVacationImage';

export function inventoryVacationShareFilename(startsOn: string): string {
    const stem = startsOn.trim() !== '' ? startsOn : 'vakantie';

    return `medibeheer-vakantie-${stem}.${INVENTORY_VACATION_SHARE_IMAGE_EXTENSION}`;
}
