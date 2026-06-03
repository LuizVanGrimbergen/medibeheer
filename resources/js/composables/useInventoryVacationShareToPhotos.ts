import { computed, ref  } from 'vue';
import type {ComputedRef} from 'vue';
import type { Composer } from 'vue-i18n';
import { createInventoryVacationShareFiles } from '@/lib/patient/inventory/createInventoryVacationShareFiles';
import { inventoryVacationShareFilename } from '@/lib/patient/inventory/inventoryVacationShareFilename';
import type { InventoryVacationShareImagePayload } from '@/lib/patient/inventory/inventoryVacationShareImageTypes';
import {
    canShareVacationImagesFromBrowser,
    shareOrDownloadInventoryVacationShareFiles,
    shareVacationImageFile,
} from '@/lib/patient/inventory/shareInventoryVacationImage';

type UseInventoryVacationShareToPhotosOptions = {
    shareImagePayload: ComputedRef<InventoryVacationShareImagePayload | null>;
    startsOn: ComputedRef<string>;
    t: Composer['t'];
};

function shareStepTitle(listTitle: string, fileIndex: number, total: number): string {
    if (total <= 1) {
        return listTitle;
    }

    return `${listTitle} (${fileIndex + 1}/${total})`;
}

export function useInventoryVacationShareToPhotos(options: UseInventoryVacationShareToPhotosOptions) {
    const isSaving = ref(false);
    const saveError = ref<string | null>(null);
    const saveShareHintVisible = ref(false);
    const savedShareImageCount = ref(1);
    const shareFiles = ref<File[]>([]);
    const shareStepIndex = ref(0);
    const shareFlowOpen = ref(false);
    const shareListTitle = ref('');

    const shareStepCurrent = computed(() => shareStepIndex.value + 1);
    const shareStepTotal = computed(() => shareFiles.value.length);

    function resetShareFlow(): void {
        shareFlowOpen.value = false;
        shareFiles.value = [];
        shareStepIndex.value = 0;
        shareListTitle.value = '';
    }

    async function shareCurrentImage(): Promise<void> {
        const files = shareFiles.value;
        const index = shareStepIndex.value;

        if (index >= files.length) {
            return;
        }

        saveError.value = null;

        try {
            const outcome = await shareVacationImageFile(
                files[index],
                shareStepTitle(shareListTitle.value, index, files.length),
            );

            if (outcome === 'aborted') {
                return;
            }

            shareStepIndex.value += 1;

            if (shareStepIndex.value >= files.length) {
                savedShareImageCount.value = files.length;
                saveShareHintVisible.value = true;
                resetShareFlow();
            }
        } catch {
            saveError.value = options.t('patient.inventory.vacationSaveFailed');
        }
    }

    async function prepareShareFiles(): Promise<void> {
        const payload = options.shareImagePayload.value;

        if (payload === null) {
            return;
        }

        saveError.value = null;
        saveShareHintVisible.value = false;
        resetShareFlow();
        isSaving.value = true;

        try {
            const filename = inventoryVacationShareFilename(options.startsOn.value);
            const files = await createInventoryVacationShareFiles(payload, filename);

            if (canShareVacationImagesFromBrowser()) {
                shareListTitle.value = payload.title;
                shareFiles.value = files;
                shareStepIndex.value = 0;
                shareFlowOpen.value = true;

                return;
            }

            const outcome = await shareOrDownloadInventoryVacationShareFiles(
                files,
                payload.title,
            );

            if (outcome === 'shared' || outcome === 'downloaded') {
                savedShareImageCount.value = files.length;
                saveShareHintVisible.value = true;
            }
        } catch {
            saveError.value = options.t('patient.inventory.vacationSaveFailed');
        } finally {
            isSaving.value = false;
        }
    }

    return {
        isSaving,
        saveError,
        saveShareHintVisible,
        savedShareImageCount,
        shareFlowOpen,
        shareStepCurrent,
        shareStepTotal,
        prepareShareFiles,
        shareCurrentImage,
    };
}
