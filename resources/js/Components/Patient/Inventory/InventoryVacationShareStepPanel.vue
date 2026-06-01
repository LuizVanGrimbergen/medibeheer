<script setup lang="ts">
import { Images } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { patientAppointmentFormPrimaryPairButtonClass } from '@/lib/patient/appointments/ui/patientSoftDangerActionButtonClass';
import { inventoryVacationShareStepPanelClass } from '@/lib/patient/inventory/inventoryVacationUiClasses';

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
    <div :class="inventoryVacationShareStepPanelClass">
        <p class="text-base leading-relaxed text-text sm:text-lg">
            {{
                t('patient.inventory.vacationShareStepPrompt', {
                    current: String(stepCurrent),
                    total: String(stepTotal),
                })
            }}
        </p>

        <p
            v-if="stepsCompleted > 0"
            class="text-sm leading-relaxed text-text-muted sm:text-base"
        >
            {{
                t('patient.inventory.vacationShareStepProgress', {
                    saved: String(stepsCompleted),
                    total: String(stepTotal),
                })
            }}
        </p>

        <Button
            type="button"
            variant="default"
            size="lg"
            :class="[patientAppointmentFormPrimaryPairButtonClass, 'w-full']"
            :aria-label="
                t('patient.inventory.vacationShareStepButton', {
                    current: String(stepCurrent),
                    total: String(stepTotal),
                })
            "
            @click="emit('share')"
        >
            <Images
                class="size-6 shrink-0"
                aria-hidden="true"
            />
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
</template>
