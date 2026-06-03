<script setup lang="ts">
import { Layers } from 'lucide-vue-next';
import type { InventoryVacationShareImageItem } from '@/lib/patient/inventory/inventoryVacationShareImageTypes';
import {
    inventoryVacationMetricBoxClass,
    inventoryVacationMetricLabelClass,
} from '@/lib/patient/inventory/inventoryVacationUiClasses';
import {
    medicationStockCurrentStockIconWrapClass,
    medicationStockCurrentStockPanelClass,
} from '@/lib/patient/inventory/medicationStockCurrentStockPanelClasses';

defineProps<{
    item: InventoryVacationShareImageItem;
}>();

const totalPanelClass = medicationStockCurrentStockPanelClass(null);
const totalIconWrapClass = medicationStockCurrentStockIconWrapClass(null);
</script>

<template>
    <div class="space-y-4">
        <div
            v-if="item.primaryValue !== null && item.secondaryValue !== null"
            class="grid grid-cols-2 gap-3"
        >
            <div :class="inventoryVacationMetricBoxClass">
                <p :class="inventoryVacationMetricLabelClass">
                    {{ item.primaryLabel }}
                </p>
                <p
                    class="mt-1 text-2xl font-bold tabular-nums leading-none text-text-heading"
                >
                    {{ item.primaryValue }}
                </p>
            </div>
            <div :class="inventoryVacationMetricBoxClass">
                <p :class="inventoryVacationMetricLabelClass">
                    {{ item.secondaryLabel }}
                </p>
                <p
                    class="mt-1 text-2xl font-bold tabular-nums leading-none text-text-heading"
                >
                    {{ item.secondaryValue }}
                </p>
            </div>
        </div>

        <div
            v-if="item.totalNumeric !== null"
            :class="totalPanelClass"
        >
            <div :class="totalIconWrapClass">
                <Layers
                    class="size-5"
                    aria-hidden="true"
                />
            </div>
            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                <span class="text-sm font-semibold leading-snug text-text-heading">
                    {{ item.totalLabel }}
                </span>
                <div class="flex items-baseline gap-2">
                    <span
                        class="text-2xl font-bold tabular-nums leading-none tracking-tight text-text-heading"
                    >
                        {{ item.totalNumeric }}
                    </span>
                    <span
                        v-if="item.totalUnitChip !== null"
                        class="text-lg font-semibold text-text-heading"
                    >
                        {{ item.totalUnitChip }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
