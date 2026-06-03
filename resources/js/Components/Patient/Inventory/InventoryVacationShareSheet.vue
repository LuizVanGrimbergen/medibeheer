<script setup lang="ts">
import InventoryVacationMetricBox from '@/Components/Patient/Inventory/InventoryVacationMetricBox.vue';
import InventoryVacationShareMedicationPickup from '@/Components/Patient/Inventory/InventoryVacationShareMedicationPickup.vue';
import { Card, CardContent } from '@/Components/ui/card';
import type { InventoryVacationShareImagePayload } from '@/lib/patient/inventory/inventoryVacationShareImageTypes';
import { INVENTORY_VACATION_SHARE_MEDICATION_DATA_ATTRIBUTE } from '@/lib/patient/inventory/inventoryVacationShareSelectors';
import {
    inventoryVacationPeriodBadgeClass,
    inventoryVacationResultsCardClass,
    inventoryVacationShareMetricGridClass,
} from '@/lib/patient/inventory/inventoryVacationUiClasses';
import {
    patientPageSectionTitleClass,
    patientPageTitleClass,
} from '@/lib/patient/patientPageTypography';

defineProps<{
    payload: InventoryVacationShareImagePayload;
}>();
</script>

<template>
    <div class="box-border w-[840px] space-y-5 bg-bg p-10 text-text">
        <header class="space-y-2">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <h1 :class="patientPageTitleClass">
                    {{ payload.title }}
                </h1>
                <p
                    v-if="payload.pageLabel !== null"
                    :class="inventoryVacationPeriodBadgeClass"
                >
                    {{ payload.pageLabel }}
                </p>
            </div>
        </header>

        <Card :class="inventoryVacationResultsCardClass">
            <CardContent class="space-y-3 p-4 sm:p-5">
                <div
                    class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1"
                >
                    <h2 :class="patientPageSectionTitleClass">
                        {{ payload.periodHeading }}
                    </h2>
                    <p
                        class="text-xl font-bold tabular-nums leading-none text-text-heading"
                    >
                        {{ payload.daysLabel }}
                    </p>
                </div>

                <dl :class="inventoryVacationShareMetricGridClass">
                    <InventoryVacationMetricBox
                        :label="payload.departureLabel"
                        :value="payload.departureDate"
                    />
                    <InventoryVacationMetricBox
                        :label="payload.returnLabel"
                        :value="payload.returnDate"
                    />
                </dl>
            </CardContent>
        </Card>

        <p
            v-if="payload.emptyMessage !== null"
            class="rounded-2xl border border-border/80 bg-surface-2/80 px-4 py-5 text-base leading-relaxed text-text md:text-lg"
        >
            {{ payload.emptyMessage }}
        </p>

        <section
            v-if="payload.items.length > 0"
            class="space-y-4"
        >
            <h2 :class="patientPageSectionTitleClass">
                {{ payload.listHeading }}
            </h2>

            <p
                v-if="payload.savedPackageHint !== null"
                class="text-base leading-relaxed text-text-muted"
            >
                {{ payload.savedPackageHint }}
            </p>

            <ul class="flex flex-col gap-4">
                <li
                    v-for="item in payload.items"
                    :key="item.name"
                    :[INVENTORY_VACATION_SHARE_MEDICATION_DATA_ATTRIBUTE]="true"
                >
                    <Card :class="inventoryVacationResultsCardClass">
                        <CardContent class="space-y-4 p-5 sm:p-6">
                            <p class="text-lg font-bold leading-snug text-text-heading">
                                {{ item.name }}
                            </p>
                            <InventoryVacationShareMedicationPickup :item="item" />
                        </CardContent>
                    </Card>
                </li>
            </ul>
        </section>

        <p
            v-if="payload.skippedNote !== null"
            class="text-sm leading-relaxed text-text-muted sm:text-base"
        >
            {{ payload.skippedNote }}
        </p>

        <footer class="text-sm text-text-muted sm:text-base">
            {{ payload.footerLabel }}
        </footer>
    </div>
</template>
