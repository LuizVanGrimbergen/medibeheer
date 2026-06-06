export type InventoryVacationImageShareOutcome = 'shared' | 'downloaded';

export type InventoryVacationImageShareStepOutcome =
    | 'shared'
    | 'aborted'
    | 'downloaded';

/** iOS Photos recognizes JPEG shares; PNG is often saved as a file instead. */
export const INVENTORY_VACATION_SHARE_IMAGE_MIME = 'image/jpeg';

export const INVENTORY_VACATION_SHARE_IMAGE_EXTENSION = 'jpg';

export function buildInventoryVacationShareFiles(
    blobs: Blob[],
    baseFilename: string,
): File[] {
    return blobs.map(
        (blob, index) =>
            new File(
                [blob],
                inventoryVacationShareFilename(
                    baseFilename,
                    index,
                    blobs.length,
                ),
                {
                    type:
                        blob.type === '' ||
                        blob.type === 'application/octet-stream'
                            ? INVENTORY_VACATION_SHARE_IMAGE_MIME
                            : blob.type,
                    lastModified: Date.now(),
                },
            ),
    );
}

function inventoryVacationShareFilename(
    baseFilename: string,
    index: number,
    total: number,
): string {
    const extension = INVENTORY_VACATION_SHARE_IMAGE_EXTENSION;
    const stem = baseFilename.replace(/\.(png|jpe?g)$/iu, '');

    if (total <= 1) {
        return `${stem}.${extension}`;
    }

    return `${stem}-${index + 1}.${extension}`;
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
    _shareTitle: string,
): Promise<InventoryVacationImageShareStepOutcome> {
    if (canShareVacationImageFile(file)) {
        try {
            // Share files only: a `title` makes iOS treat the payload as a document.
            await navigator.share({
                files: [file],
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
