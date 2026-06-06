import { toBlob } from 'html-to-image';
import { createApp, nextTick } from 'vue';
import InventoryVacationShareSheet from '@/Components/Patient/Inventory/InventoryVacationShareSheet.vue';
import { i18n } from '@/i18n';
import type { InventoryVacationShareImagePayload } from '@/lib/patient/inventory/inventoryVacationShareImageTypes';
import { INVENTORY_VACATION_SHARE_IMAGE_MIME } from '@/lib/patient/inventory/shareInventoryVacationImage';
import { inventoryVacationShareMedicationSelector } from '@/lib/patient/inventory/inventoryVacationShareSelectors';

const SHARE_IMAGE_WIDTH_PX = 840;
const SHARE_IMAGE_PIXEL_RATIO = 2;

function resolveShareImageBackgroundColor(captureTarget: HTMLElement): string {
    const computedBackground = getComputedStyle(captureTarget).backgroundColor;

    if (
        computedBackground !== '' &&
        computedBackground !== 'rgba(0, 0, 0, 0)' &&
        computedBackground !== 'transparent'
    ) {
        return computedBackground;
    }

    const themeBackground = getComputedStyle(document.documentElement)
        .getPropertyValue('--color-bg')
        .trim();

    if (themeBackground !== '') {
        return themeBackground;
    }

    throw new Error('Could not resolve share image background color.');
}

export async function waitForShareCapturePaint(): Promise<void> {
    await document.fonts.ready;
    await new Promise<void>((resolve) => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                resolve();
            });
        });
    });
}

function shareSheetCaptureTarget(host: HTMLElement): HTMLElement {
    return host.firstElementChild instanceof HTMLElement
        ? host.firstElementChild
        : host;
}

export async function waitForShareSheetMedicationCount(
    host: HTMLElement,
    expectedCount: number,
): Promise<void> {
    const captureTarget = shareSheetCaptureTarget(host);

    for (let attempt = 0; attempt < 30; attempt += 1) {
        const renderedCount = captureTarget.querySelectorAll(
            inventoryVacationShareMedicationSelector,
        ).length;

        if (renderedCount === expectedCount) {
            await waitForShareCapturePaint();

            return;
        }

        await nextTick();
        await waitForShareCapturePaint();
    }

    const renderedCount = captureTarget.querySelectorAll(
        inventoryVacationShareMedicationSelector,
    ).length;

    throw new Error(
        `Share sheet expected ${expectedCount} medications but rendered ${renderedCount}.`,
    );
}

function positionShareHostForCapture(host: HTMLElement): () => void {
    const previousCssText = host.style.cssText;

    host.style.cssText = [
        'position: fixed',
        'top: 0',
        'left: -10000px',
        `width: ${SHARE_IMAGE_WIDTH_PX}px`,
        'margin: 0',
        'padding: 0',
        'opacity: 1',
        'visibility: visible',
        'pointer-events: none',
        'transform: none',
        'z-index: -1',
    ].join('; ');

    return () => {
        host.style.cssText = previousCssText;
    };
}

export async function captureInventoryVacationSharePage(
    payload: InventoryVacationShareImagePayload,
): Promise<Blob> {
    const host = document.createElement('div');
    host.setAttribute('aria-hidden', 'true');
    document.body.appendChild(host);

    const app = createApp(InventoryVacationShareSheet, { payload });
    app.use(i18n);
    app.mount(host);

    const restoreHostPosition = positionShareHostForCapture(host);

    try {
        await waitForShareSheetMedicationCount(host, payload.items.length);

        const captureTarget = shareSheetCaptureTarget(host);
        const captureHeight = captureTarget.scrollHeight;

        const blob = await toBlob(captureTarget, {
            width: captureTarget.offsetWidth,
            height:
                captureHeight > 0 ? captureHeight : captureTarget.offsetHeight,
            pixelRatio: SHARE_IMAGE_PIXEL_RATIO,
            backgroundColor: resolveShareImageBackgroundColor(captureTarget),
            cacheBust: true,
            type: INVENTORY_VACATION_SHARE_IMAGE_MIME,
            quality: 0.92,
        });

        if (blob === null) {
            throw new Error('Could not create image.');
        }

        return blob;
    } finally {
        restoreHostPosition();
        app.unmount();
        host.remove();
    }
}
