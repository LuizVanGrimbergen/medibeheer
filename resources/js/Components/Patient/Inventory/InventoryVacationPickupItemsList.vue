<script setup lang="ts">
import InventoryVacationPickupBoxCalculator from '@/Components/Patient/Inventory/InventoryVacationPickupBoxCalculator.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { inventoryVacationResultsCardClass } from '@/lib/patient/inventory/inventoryVacationUiClasses';
import type { PatientInventoryVacationSupplyItem } from '@/lib/patient/inventory/patientInventoryVacationSupply';

defineProps<{
    items: PatientInventoryVacationSupplyItem[];
    doseUnitForItem: (doseUnit: string | null) => string | null;
}>();
</script>

<template>
    <ul class="flex flex-col gap-4">
        <li v-for="item in items" :key="item.medication_id">
            <Card :class="inventoryVacationResultsCardClass">
                <CardContent class="space-y-4 p-5 sm:p-6">
                    <p
                        class="text-text-heading text-lg leading-snug font-bold"
                    >
                        {{ item.name }}
                    </p>
                    <InventoryVacationPickupBoxCalculator
                        :id-prefix="`vacation-pickup-${item.medication_id}`"
                        :dose-unit="doseUnitForItem(item.dose_unit)"
                        :medication-type="item.type_medication"
                        :pickup-quantity="item.pickup_quantity"
                        :stock-pieces-per-package="item.stock_pieces_per_package"
                    />
                </CardContent>
            </Card>
        </li>
    </ul>
</template>
