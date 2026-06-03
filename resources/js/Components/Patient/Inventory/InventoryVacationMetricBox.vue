<script setup lang="ts">
import {
    inventoryVacationMetricBoxClass,
    inventoryVacationMetricLabelClass,
    inventoryVacationMetricValueClass,
} from '@/lib/patient/inventory/inventoryVacationUiClasses';

defineProps<{
    label: string;
    value: string;
}>();
</script>

<template>
    <div :class="inventoryVacationMetricBoxClass">
        <dt :class="inventoryVacationMetricLabelClass">
            {{ label }}
        </dt>
        <dd :class="inventoryVacationMetricValueClass">
            {{ value }}
        </dd>
    </div>
</template>
