<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationCard from '@/Components/Medications/MedicationCard.vue';
import ActivePatientBadge from '@/Components/Family/ActivePatientBadge.vue';
import PatientMedicationIntakeHistorySection from '@/Components/Patient/Medications/PatientMedicationIntakeHistorySection.vue';
import { Card, CardContent } from '@/Components/ui/card';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyMedicationsScreenProps } from '@/lib/family/medications/familyMedicationsScreenProps';
import { compareMedicationInventoryListItems } from '@/lib/patient/inventory/medicationInventoryListSortRank';
import type { MedicationListItem } from '@/lib/types';

const props = defineProps<FamilyMedicationsScreenProps>();

const { t } = useI18n();

const medicationRegisterListStatusRank = (medication: MedicationListItem): number => {
    if (medication.list_status === 'active') {
        return 0;
    }

    if (medication.list_status === 'ended') {
        return 1;
    }

    return 2;
};

const sortedRegisterMedications = computed((): MedicationListItem[] => {
    const items = [...props.medications.data];

    items.sort((left, right) => {
        const statusRank =
            medicationRegisterListStatusRank(left) - medicationRegisterListStatusRank(right);

        if (statusRank !== 0) {
            return statusRank;
        }

        return compareMedicationInventoryListItems(left, right);
    });

    return items;
});
</script>

<template>
    <Head>
        <title>{{ t('family.medications.title') }}</title>
    </Head>

    <FamilyLayout>
        <div class="flex flex-col gap-6">
            <div class="space-y-2">
                <h1 class="text-2xl font-semibold text-text-heading">
                    {{ t('family.medications.heading') }}
                </h1>
                <ActivePatientBadge :family="props.family" />
            </div>

            <p
                v-if="!props.family.has_linked_patient"
                class="max-w-prose text-sm leading-relaxed text-text-muted"
            >
                {{ t('family.medications.notLinked') }}
            </p>

            <template v-else>
                <PatientMedicationIntakeHistorySection
                    :calendar-month="props.medication_calendar_month"
                    :calendar-days="props.medication_calendar_days"
                    :calendar-slots="props.medication_calendar_slots"
                    navigate-route-name="family.medications"
                    selected-day-heading-key="patient.medications.history.selectedDayHeading"
                    selected-day-no-schedule-key="patient.medications.history.selectedDayNoSchedule"
                    selected-day-no-intakes-key="patient.medications.history.selectedDayNoIntakes"
                />

                <section class="space-y-4">
                    <h2 class="text-lg font-semibold text-text-heading">
                        {{ t('patient.medications.history.registerHeading') }}
                    </h2>

                    <ul
                        v-if="props.medications.data.length > 0"
                        class="flex min-w-0 w-full flex-col gap-6"
                    >
                        <li
                            v-for="medication in sortedRegisterMedications"
                            :key="medication.id"
                            class="min-w-0"
                        >
                        <MedicationCard
                            :medication="medication"
                            :show-actions="false"
                            show-stock
                            stock-update-route-name="family.medications.stocks.update"
                            list-status-ended-label-key="family.medications.listStatus.ended"
                            list-status-removed-label-key="family.medications.listStatus.removed"
                        />
                        </li>
                    </ul>

                    <NumberedPagination
                        v-if="
                            props.medications.data.length > 0 &&
                                props.medications.meta.last_page > 1
                        "
                        route-name="family.medications"
                        :meta="props.medications.meta"
                        :query="{ calendar_month: props.medication_calendar_month }"
                    />

                    <Card
                        v-if="props.medications.meta.total === 0"
                        class="border-border"
                    >
                        <CardContent class="py-10 text-sm text-text-muted">
                            {{ t('family.medications.empty') }}
                        </CardContent>
                    </Card>
                </section>
            </template>
        </div>
    </FamilyLayout>
</template>
