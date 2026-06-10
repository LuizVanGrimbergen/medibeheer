<script setup lang="ts">
import { Images, Loader2 } from 'lucide-vue-next';
import { nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import InventoryVacationShareStepPanel from '@/Components/Patient/Inventory/InventoryVacationShareStepPanel.vue';
import { Button } from '@/Components/ui/button';
import { mobileShellFormWizardFooterPrimaryButtonClass } from '@/lib/shell/mobileShellDialogLayout';

const props = defineProps<{
    isSaving: boolean;
    saveError: string | null;
    saveShareHintVisible: boolean;
    savedShareImageCount: number;
    shareFlowOpen: boolean;
    shareStepCurrent: number;
    shareStepTotal: number;
    plannedShareImageCount: number;
    prepareShareFiles: () => Promise<void>;
    shareCurrentImage: () => Promise<void>;
}>();

const { t } = useI18n();

const shareStepPanelRef = ref<HTMLElement | null>(null);

watch(
    () => props.shareFlowOpen,
    async (open) => {
        if (!open) {
            return;
        }

        await nextTick();
        shareStepPanelRef.value?.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
        });
    },
);
</script>

<template>
    <div class="space-y-3">
        <div v-if="shareFlowOpen" ref="shareStepPanelRef">
            <InventoryVacationShareStepPanel
                :step-current="shareStepCurrent"
                :step-total="shareStepTotal"
                :steps-completed="shareStepCurrent - 1"
                @share="shareCurrentImage"
            />
        </div>

        <Button
            v-else
            type="button"
            variant="default"
            size="lg"
            :class="mobileShellFormWizardFooterPrimaryButtonClass"
            :disabled="isSaving"
            :aria-label="
                plannedShareImageCount > 1
                    ? t('patient.inventory.vacationSaveToPhotosMultiple', {
                          count: String(plannedShareImageCount),
                      })
                    : t('patient.inventory.vacationSaveToPhotos')
            "
            @click="prepareShareFiles"
        >
            <Loader2
                v-if="isSaving"
                class="size-6 shrink-0 animate-spin"
                aria-hidden="true"
            />
            <Images v-else class="size-6 shrink-0" aria-hidden="true" />
            <span>
                {{
                    isSaving
                        ? t('patient.inventory.vacationSaving')
                        : plannedShareImageCount > 1
                          ? t(
                                'patient.inventory.vacationSaveToPhotosMultiple',
                                {
                                    count: String(plannedShareImageCount),
                                },
                            )
                          : t('patient.inventory.vacationSaveToPhotos')
                }}
            </span>
        </Button>

        <p
            v-if="saveError !== null"
            class="text-destructive text-sm leading-relaxed sm:text-base"
            role="alert"
        >
            {{ saveError }}
        </p>

        <p
            v-else-if="saveShareHintVisible"
            class="text-text-muted text-sm leading-relaxed sm:text-base"
        >
            {{
                savedShareImageCount > 1
                    ? t('patient.inventory.vacationSaveShareHintMultiple', {
                          count: String(savedShareImageCount),
                      })
                    : t('patient.inventory.vacationSaveShareHint')
            }}
        </p>
    </div>
</template>
