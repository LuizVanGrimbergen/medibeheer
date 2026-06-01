<script setup lang="ts">
import InventoryVacationMetricBox from '@/Components/Patient/Inventory/InventoryVacationMetricBox.vue';
import { inventoryVacationCalculationMetricGridClass } from '@/lib/patient/inventory/inventoryVacationUiClasses';

defineProps<{
    neededLabel: string;
    neededValue: string;
    currentStockLabel: string;
    currentStockValue: string;
    pickupLabel: string;
    pickupValue: string;
    formulaText: string;
    metricGridClass?: string;
}>();
</script>

<template>
    <div class="space-y-2">
        <dl :class="metricGridClass ?? inventoryVacationCalculationMetricGridClass">
            <InventoryVacationMetricBox
                :label="neededLabel"
                :value="neededValue"
            />
            <InventoryVacationMetricBox
                :label="currentStockLabel"
                :value="currentStockValue"
            />
            <InventoryVacationMetricBox
                :label="pickupLabel"
                :value="pickupValue"
            />
        </dl>
        <p class="text-sm leading-relaxed text-text-muted sm:text-base">
            {{ formulaText }}
        </p>
    </div>
</template>
