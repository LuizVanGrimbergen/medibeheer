<script setup lang="ts">
import { Images } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import PatientShellWizardFooterCard from '@/Components/Patient/form/PatientShellWizardFooterCard.vue';
import { Button } from '@/Components/ui/button';
import { INVENTORY_VACATION_MEDICATIONS_PER_SHARE_PAGE } from '@/lib/patient/inventory/splitInventoryVacationShareImagePayloads';
import {
    patientFormWizardFooterPrimaryButtonClass,
    patientShellPageDescriptionClass,
} from '@/lib/patient/patientShellDialogLayout';

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
    <PatientShellWizardFooterCard>
        <div class="flex flex-col gap-3 sm:gap-4">
            <div class="space-y-1.5">
                <p :class="patientShellPageDescriptionClass">
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
                :class="patientFormWizardFooterPrimaryButtonClass"
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
    </PatientShellWizardFooterCard>
</template>
