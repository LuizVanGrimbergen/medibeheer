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
        class="min-w-0 w-full rounded-3xl border bg-surface text-text shadow-md shadow-black/[0.04]"
        :class="inventoryVisualToneClasses.border"
    >
        <CardContent class="relative space-y-6 p-6 sm:p-7">
            <div class="space-y-4">
                <div class="flex min-w-0 items-start gap-4">
                    <div
                        class="flex size-12 shrink-0 items-center justify-center rounded-xl"
                        :class="inventoryVisualToneClasses.pillWrap"
                    >
                        <span class="sr-only">{{ typeLabel }}</span>
                        <MedicationTypeLeadIcon
                            :medication-type="medication.type_medication"
                            :icon-tone-class="inventoryVisualToneClasses.pillIcon"
                        />
                    </div>
                    <div class="min-w-0 flex-1 overflow-hidden space-y-1">
                        <p
                            class="text-lg font-bold leading-snug text-text-heading sm:text-xl"
                        >
                            {{ medication.name }}
                        </p>
                        <p class="text-base font-normal leading-snug text-text-muted">
                            {{ typeLabel }}
                        </p>
                    </div>
                </div>

                <MedicationStockControls
                    :medication="medication"
                    :update-route-name="props.updateRouteName"
                    :id-prefix="`patient-inventory-stock-${medication.id}`"
                    class="border-t border-border/70 pt-5"
                />
            </div>
        </CardContent>
    </Card>
</template>
