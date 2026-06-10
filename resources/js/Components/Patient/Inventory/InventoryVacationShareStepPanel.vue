<script setup lang="ts">
import { Images } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import MobileShellWizardFooterCard from '@/Components/shell/MobileShellWizardFooterCard.vue';
import { Button } from '@/Components/ui/button';
import { INVENTORY_VACATION_MEDICATIONS_PER_SHARE_PAGE } from '@/lib/patient/inventory/splitInventoryVacationShareImagePayloads';
import {
    mobileShellFormWizardFooterPrimaryButtonClass,
    mobileShellDialogDescriptionClass,
} from '@/lib/shell/mobileShellDialogLayout';

defineProps<{
    stepCurrent: number;
    stepTotal: number;
    stepsCompleted: number;
}>();

const emit = defineEmits<{
    share: [];
}>();

const { t } = useI18n();
</script>

<template>
    <MobileShellWizardFooterCard>
        <div class="flex flex-col gap-3 sm:gap-4">
            <div class="space-y-1.5">
                <p :class="mobileShellDialogDescriptionClass">
                    {{
                        t('patient.inventory.vacationShareStepPrompt', {
                            current: String(stepCurrent),
                            total: String(stepTotal),
                            perPage: String(
                                INVENTORY_VACATION_MEDICATIONS_PER_SHARE_PAGE,
                            ),
                        })
                    }}
                </p>

                <p
                    v-if="stepsCompleted > 0"
                    class="text-text-muted text-sm leading-relaxed sm:text-base"
                >
                    {{
                        t('patient.inventory.vacationShareStepProgress', {
                            saved: String(stepsCompleted),
                            total: String(stepTotal),
                        })
                    }}
                </p>
            </div>

            <Button
                type="button"
                variant="default"
                size="lg"
                :class="mobileShellFormWizardFooterPrimaryButtonClass"
                :aria-label="
                    t('patient.inventory.vacationShareStepButton', {
                        current: String(stepCurrent),
                        total: String(stepTotal),
                    })
                "
                @click="emit('share')"
            >
                <Images class="size-6 shrink-0" aria-hidden="true" />
                <span>
                    {{
                        t('patient.inventory.vacationShareStepButton', {
                            current: String(stepCurrent),
                            total: String(stepTotal),
                        })
                    }}
                </span>
            </Button>
        </div>
    </MobileShellWizardFooterCard>
</template>
