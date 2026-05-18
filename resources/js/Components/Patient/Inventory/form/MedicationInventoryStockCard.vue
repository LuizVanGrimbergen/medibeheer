<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationStockControls from '@/Components/Medications/MedicationStockControls.vue';
import MedicationTypeLeadIcon from '@/Components/Medications/MedicationTypeLeadIcon.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationListVisualToneClasses } from '@/lib/patient/inventory/medicationListVisualToneClasses';
import type { MedicationListItem, MedicationTypeValue } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        medication: MedicationListItem;
        updateRouteName?: string;
    }>(),
    {
        updateRouteName: 'patient.medications.stocks.update',
    },
);

const { t } = useI18n();

const stockProgressTone = computed(() => medicationListVisualTone(props.medication));

const inventoryVisualToneClasses = computed(() =>
    medicationListVisualToneClasses(stockProgressTone.value),
);

const typeLabel = computed(() =>
    t(`patient.medications.types.${props.medication.type_medication as MedicationTypeValue}`),
);
</script>

<template>
    <Card
        class="min-w-0 w-full rounded-3xl border-2 bg-surface text-text shadow-md shadow-black/[0.04]"
        :class="inventoryVisualToneClasses.border"
    >
        <CardContent class="relative p-4 pb-6 pt-5 sm:p-8">
            <div
                class="flex min-w-0 w-full flex-col gap-5 sm:flex-row sm:items-start sm:gap-6"
            >
                <div
                    class="flex size-12 shrink-0 items-center justify-center rounded-2xl sm:size-16"
                    :class="inventoryVisualToneClasses.pillWrap"
                >
                    <span class="sr-only">{{ typeLabel }}</span>
                    <MedicationTypeLeadIcon
                        :medication-type="medication.type_medication"
                        :icon-tone-class="inventoryVisualToneClasses.pillIcon"
                    />
                </div>
                <div
                    class="flex w-full min-w-0 flex-col space-y-3.5 sm:flex-1 sm:items-stretch"
                >
                    <p
                        class="min-w-0 wrap-break-word text-lg font-bold leading-snug text-text-heading sm:text-xl"
                    >
                        {{ medication.name }}
                    </p>

                    <MedicationStockControls
                        :medication="medication"
                        :update-route-name="props.updateRouteName"
                        :id-prefix="`patient-inventory-stock-${medication.id}`"
                    />
                </div>
            </div>
        </CardContent>
    </Card>
</template>
