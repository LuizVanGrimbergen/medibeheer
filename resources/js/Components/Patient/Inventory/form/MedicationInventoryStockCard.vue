<script setup lang="ts">
import { computed } from 'vue';
import MedicationListCardLead from '@/Components/Medications/MedicationListCardLead.vue';
import MedicationStockControls from '@/Components/Medications/MedicationStockControls.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationUrgencyToneClasses } from '@/lib/patient/medications/urgency/medicationUrgencyToneClasses';
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

const stockProgressTone = computed(() =>
    medicationListVisualTone(props.medication),
);

const inventoryVisualToneClasses = computed(() =>
    medicationUrgencyToneClasses(stockProgressTone.value),
);
</script>

<template>
    <Card
        class="bg-surface text-text w-full min-w-0 rounded-3xl border shadow-md shadow-black/[0.04]"
        :class="inventoryVisualToneClasses.border"
    >
        <CardContent class="relative space-y-6 p-6 sm:p-7">
            <div class="space-y-4">
                <MedicationListCardLead
                    :name="medication.name"
                    :type-medication="
                        medication.type_medication as MedicationTypeValue
                    "
                    :tone="stockProgressTone"
                />

                <MedicationStockControls
                    :medication="medication"
                    :update-route-name="props.updateRouteName"
                    :id-prefix="`patient-inventory-stock-${medication.id}`"
                    class="border-border/70 border-t pt-5"
                />
            </div>
        </CardContent>
    </Card>
</template>
