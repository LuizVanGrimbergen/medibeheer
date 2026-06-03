export type InventoryVacationImageShareOutcome = 'shared' | 'downloaded';

export type InventoryVacationImageShareStepOutcome = 'shared' | 'aborted' | 'downloaded';

export function buildInventoryVacationShareFiles(
    blobs: Blob[],
    baseFilename: string,
): File[] {
    return blobs.map(
        (blob, index) =>
            new File(
                [blob],
                inventoryVacationShareFilename(baseFilename, index, blobs.length),
                { type: 'image/png' },
            ),
    );
}

function inventoryVacationShareFilename(baseFilename: string, index: number, total: number): string {
    if (total <= 1) {
        return baseFilename.endsWith('.png') ? baseFilename : `${baseFilename}.png`;
    }

    const stem = baseFilename.replace(/\.png$/iu, '');

    return `${stem}-${index + 1}.png`;
}

function downloadBlob(blob: Blob, filename: string): void {
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = filename;
    anchor.rel = 'noopener';
    anchor.click();
    URL.revokeObjectURL(url);
}

export function canShareVacationImageFile(file: File): boolean {
    if (typeof navigator.share !== 'function') {
        return false;
    }

    if (typeof navigator.canShare !== 'function') {
        return true;
    }

    return navigator.canShare({ files: [file] });
}

export function canShareVacationImagesFromBrowser(): boolean {
    return typeof navigator.share === 'function';
}

export async function shareVacationImageFile(
    file: File,
    shareTitle: string,
): Promise<InventoryVacationImageShareStepOutcome> {
    if (canShareVacationImageFile(file)) {
        try {
            await navigator.share({
                files: [file],
                title: shareTitle,
            });

            return 'shared';
        } catch (error) {
            if (error instanceof DOMException && error.name === 'AbortError') {
                return 'aborted';
            }

            throw error;
        }
    }

    downloadBlob(file, file.name);

    return 'downloaded';
}

function downloadFilesOneByOne(files: File[]): void {
    for (const file of files) {
        downloadBlob(file, file.name);
    }
}

export async function shareOrDownloadInventoryVacationShareFiles(
    files: File[],
    shareTitle: string,
): Promise<InventoryVacationImageShareOutcome> {
    if (files.length === 0) {
        throw new Error('No images to share.');
    }

    if (files.length === 1) {
        const outcome = await shareVacationImageFile(files[0], shareTitle);

        if (outcome === 'aborted') {
            return 'shared';
        }

        return outcome === 'shared' ? 'shared' : 'downloaded';
    }

    downloadFilesOneByOne(files);

    return 'downloaded';
}

export async function shareOrDownloadInventoryVacationImages(
    blobs: Blob[],
    baseFilename: string,
    shareTitle: string,
): Promise<InventoryVacationImageShareOutcome> {
    const files = buildInventoryVacationShareFiles(blobs, baseFilename);

    return shareOrDownloadInventoryVacationShareFiles(files, shareTitle);
}
