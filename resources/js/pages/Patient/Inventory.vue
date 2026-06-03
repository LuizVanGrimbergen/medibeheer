<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationInventoryStockCard from '@/Components/Patient/Inventory/form/MedicationInventoryStockCard.vue';
import InventoryPageIntro from '@/Components/Patient/Inventory/InventoryPageIntro.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import { Card, CardContent } from '@/Components/ui/card';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
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
        <meta
            name="description"
            :content="t('patient.inventory.metaDescription')"
        />
    </Head>

    <PatientLayout>
        <PatientPageShell :title="t('patient.inventory.listHeading')">
            <InventoryPageIntro
                :show-vacation-button="props.medications.meta.total > 0"
            />

            <section class="space-y-5">
                <ul
                    v-if="props.medications.data.length > 0"
                    class="flex w-full min-w-0 flex-col gap-6 sm:gap-7"
                >
                    <li
                        v-for="medication in sortedInventoryMedications"
                        :key="medication.id"
                        class="min-w-0"
                    >
                        <MedicationInventoryStockCard
                            :medication="medication"
                        />
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
                    class="border-border bg-surface-2/70 text-text rounded-2xl border-2 border-dashed shadow-none"
                >
                    <CardContent
                        class="text-text-muted px-5 py-14 text-center text-lg leading-relaxed sm:px-8"
                    >
                        {{ t('patient.inventory.empty') }}
                    </CardContent>
                </Card>
            </section>
        </PatientPageShell>
    </PatientLayout>
</template>
