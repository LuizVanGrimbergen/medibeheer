<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Palmtree } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import MedicationInventoryStockCard from '@/Components/Patient/Inventory/form/MedicationInventoryStockCard.vue';
import PatientDeferredListSection from '@/Components/Patient/PatientDeferredListSection.vue';
import PatientPageIntroActionBar from '@/Components/Patient/PatientPageIntroActionBar.vue';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import { Button } from '@/Components/ui/button';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import { usePatientInventoryPage } from '@/composables/patient/usePatientInventoryPage';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import type { PatientInventoryScreenProps } from '@/lib/patient/inventory/patientInventoryScreenProps';
import { mobileShellPageIntroButtonClass } from '@/lib/shell/mobileShellTypography';

const props = defineProps<PatientInventoryScreenProps>();

const { t } = useI18n();

const {
    isInventoryLoading,
    sortedInventoryMedications,
    showInventoryEmpty,
    showVacationButton,
} = usePatientInventoryPage(props);
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
            <PatientPageIntroActionBar :show="showVacationButton">
                <Button as-child size="lg" :class="mobileShellPageIntroButtonClass">
                    <Link :href="route('patient.inventory.vacation')">
                        <Palmtree class="size-6 shrink-0" aria-hidden="true" />
                        {{ t('patient.inventory.vacationButton') }}
                    </Link>
                </Button>
            </PatientPageIntroActionBar>

            <PatientDeferredListSection
                :loading="isInventoryLoading"
                :show-empty="showInventoryEmpty"
                :empty-message="t('patient.inventory.empty')"
            >
                <ul
                    v-if="sortedInventoryMedications.length > 0"
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

                <template #pagination>
                    <NumberedPagination
                        v-if="
                            props.medications !== undefined &&
                            props.medications.data.length > 0 &&
                            props.medications.meta.last_page > 1
                        "
                        route-name="patient.inventory"
                        :meta="props.medications.meta"
                    />
                </template>
            </PatientDeferredListSection>
        </PatientPageShell>
    </PatientLayout>
</template>
