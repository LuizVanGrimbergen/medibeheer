<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationInventoryStockCard from '@/Components/Patient/Inventory/form/MedicationInventoryStockCard.vue';
import { Card, CardContent } from '@/Components/ui/card';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { compareMedicationInventoryListItems } from '@/lib/patient/inventory/medicationInventoryListSortRank';
import type { PatientInventoryScreenProps } from '@/lib/patient/inventory/patientInventoryScreenProps';
import type { MedicationListItem } from '@/lib/types';

const props = defineProps<PatientInventoryScreenProps>();

const { t } = useI18n();

const sortedInventoryMedications = computed((): MedicationListItem[] => {
    const items = [...props.medications.data];

    items.sort(compareMedicationInventoryListItems);

    return items;
});
</script>

<template>
    <Head>
        <title>{{ t('patient.inventory.title') }}</title>
    </Head>

    <PatientLayout>
        <PatientPageShell :title="t('patient.inventory.listHeading')">
            <section class="flex min-w-0 w-full flex-col space-y-5">
            <h1 class="text-3xl font-bold leading-tight text-text-heading sm:text-4xl sm:leading-tight">
                {{ t('patient.inventory.listHeading') }}
            </h1>

            <ul
                v-if="props.medications.data.length > 0"
                class="flex min-w-0 w-full flex-col gap-6 sm:gap-7"
            >
                <li
                    v-for="medication in sortedInventoryMedications"
                    :key="medication.id"
                    class="min-w-0"
                >
                    <MedicationInventoryStockCard :medication="medication" />
                </li>
            </ul>

            <NumberedPagination
                v-if="
                    props.medications.data.length > 0 &&
                        props.medications.meta.last_page > 1
                "
                route-name="patient.inventory"
                :meta="props.medications.meta"
            />

            <Card
                v-if="props.medications.meta.total === 0"
                class="rounded-2xl border-2 border-dashed border-border bg-surface-2/70 text-text shadow-none"
            >
                <CardContent
                    class="px-5 py-14 text-center text-lg leading-relaxed text-text-muted sm:px-8"
                >
                    {{ t('patient.inventory.empty') }}
                </CardContent>
            </Card>
            </section>
        </PatientPageShell>
    </PatientLayout>
</template>
