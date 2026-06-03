<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, nextTick, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import MedicationCard from '@/Components/Medications/MedicationCard.vue';
import PatientMedicationIntakeHistorySection from '@/Components/Patient/Medications/PatientMedicationIntakeHistorySection.vue';
import { Card, CardContent } from '@/Components/ui/card';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyMedicationsScreenProps } from '@/lib/family/medications/familyMedicationsScreenProps';
import { readFamilyScreenQueryParam } from '@/lib/family/readFamilyScreenQueryParam';
import { compareMedicationInventoryListItems } from '@/lib/patient/inventory/medicationInventoryListSortRank';
import type { MedicationListItem } from '@/lib/types';

const props = defineProps<FamilyMedicationsScreenProps>();

const { t } = useI18n();
const page = usePage();

const paginationQuery = computed((): Record<string, string | number> => {
    const query: Record<string, string | number> = {
        calendar_month: props.medication_calendar_month,
    };

    const medication = readFamilyScreenQueryParam('medication', page.url);

    if (medication !== null) {
        query.medication = medication;
    }

    return query;
});

function scrollToDeepLinkedMedication(): void {
    const medicationId = readFamilyScreenQueryParam('medication', page.url);

    if (medicationId === null) {
        return;
    }

    const id = Number(medicationId);

    if (!props.medications.data.some((medication) => medication.id === id)) {
        return;
    }

    nextTick(() => {
        document
            .getElementById(`family-medication-${id}`)
            ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}

watch(
    () => [page.url, props.medications.data] as const,
    () => {
        scrollToDeepLinkedMedication();
    },
    { immediate: true, deep: true },
);

const medicationRegisterListStatusRank = (
    medication: MedicationListItem,
): number => {
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
            medicationRegisterListStatusRank(left) -
            medicationRegisterListStatusRank(right);

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
        <FamilyPageShell
            :title="t('family.medications.heading')"
            :family="props.family"
            :show-active-patient="props.family.has_linked_patient"
        >
            <p
                v-if="!props.family.has_linked_patient"
                class="text-text-muted max-w-prose text-sm leading-relaxed"
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
                    slot-card-density="compact"
                />

                <section class="space-y-4">
                    <h2
                        class="text-text-heading text-lg font-semibold md:text-base"
                    >
                        {{ t('patient.medications.history.registerHeading') }}
                    </h2>

                    <ul
                        v-if="props.medications.data.length > 0"
                        class="flex w-full min-w-0 flex-col gap-6 md:gap-4"
                    >
                        <li
                            v-for="medication in sortedRegisterMedications"
                            :id="`family-medication-${medication.id}`"
                            :key="medication.id"
                            class="min-w-0 scroll-mt-6"
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
                        :query="paginationQuery"
                    />

                    <Card
                        v-if="props.medications.meta.total === 0"
                        class="border-border"
                    >
                        <CardContent class="text-text-muted py-10 text-sm">
                            {{ t('family.medications.empty') }}
                        </CardContent>
                    </Card>
                </section>
            </template>
        </FamilyPageShell>
    </FamilyLayout>
</template>
