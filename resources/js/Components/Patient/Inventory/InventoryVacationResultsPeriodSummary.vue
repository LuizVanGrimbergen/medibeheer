<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import InventoryVacationMetricBox from '@/Components/Patient/Inventory/InventoryVacationMetricBox.vue';
import { Card, CardContent } from '@/Components/ui/card';
import {
    inventoryVacationMetricGridClass,
    inventoryVacationResultsCardClass,
} from '@/lib/patient/inventory/inventoryVacationUiClasses';
import { mobileShellPageSectionTitleClass } from '@/lib/shell/mobileShellTypography';

defineProps<{
    vacationDaysLabel: string;
    formattedStartsOn: string;
    formattedEndsOn: string;
}>();

const { t } = useI18n();
</script>

<template>
    <Card :class="inventoryVacationResultsCardClass">
        <CardContent class="space-y-3 p-4 sm:p-5">
            <div
                class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1"
            >
                <h2 :class="mobileShellPageSectionTitleClass">
                    {{ t('patient.inventory.vacationResultsPeriodHeading') }}
                </h2>
                <p
                    class="text-text-heading text-xl leading-none font-bold tabular-nums"
                >
                    {{ vacationDaysLabel }}
                </p>
            </div>

            <dl :class="inventoryVacationMetricGridClass">
                <InventoryVacationMetricBox
                    :label="
                        t('patient.inventory.vacationResultsDepartureLabel')
                    "
                    :value="formattedStartsOn"
                />
                <InventoryVacationMetricBox
                    :label="t('patient.inventory.vacationResultsReturnLabel')"
                    :value="formattedEndsOn"
                />
            </dl>
        </CardContent>
    </Card>
</template>
