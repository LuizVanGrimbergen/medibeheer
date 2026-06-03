import { captureInventoryVacationSharePage } from '@/lib/patient/inventory/captureInventoryVacationShareImage';
import type { InventoryVacationShareImagePayload } from '@/lib/patient/inventory/inventoryVacationShareImageTypes';
import { buildInventoryVacationShareFiles } from '@/lib/patient/inventory/shareInventoryVacationImage';
import { splitInventoryVacationShareImagePayloads } from '@/lib/patient/inventory/splitInventoryVacationShareImagePayloads';

export async function createInventoryVacationShareFiles(
    payload: InventoryVacationShareImagePayload,
    baseFilename: string,
): Promise<File[]> {
    const pages = splitInventoryVacationShareImagePayloads(payload);
    const blobs: Blob[] = [];

    for (const page of pages) {
        blobs.push(await captureInventoryVacationSharePage(page));
    }

    return buildInventoryVacationShareFiles(blobs, baseFilename);
}
