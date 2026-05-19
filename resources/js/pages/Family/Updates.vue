<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import ActivePatientBadge from '@/Components/Family/ActivePatientBadge.vue';
import FamilyUpdatesMedicationDayGroup from '@/Components/Family/Updates/FamilyUpdatesMedicationDayGroup.vue';
import FamilyWellbeingCheckinCard from '@/Components/Family/Wellbeing/FamilyWellbeingCheckinCard.vue';
import { SegmentedToggle } from '@/Components/ui/segmented-toggle';
import { setUpdatesPeriodDaysFromToggle } from '@/composables/useFamilyUpdatesActions';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import { groupMedicationSlotsByDate } from '@/lib/family/updates/groupMedicationSlotsByDate';
import type { FamilyUpdatesScreenProps } from '@/lib/family/updates/familyUpdatesScreenProps';
import type { FamilyDashboardProps } from '@/lib/types';

const props = defineProps<
    FamilyUpdatesScreenProps & {
        family: FamilyDashboardProps;
    }
>();

const { t } = useI18n();

const periodToggleValue = computed((): string => String(props.updates_period_days));

const medicationSlotsByDate = computed(() =>
    groupMedicationSlotsByDate(props.updates_medication_slots),
);

const hasUpdates = computed(
    (): boolean =>
        props.updates_checkins.length > 0 || props.updates_medication_slots.length > 0,
);

const showSectionHeadings = computed(
    (): boolean =>
        props.updates_checkins.length > 0 && props.updates_medication_slots.length > 0,
);

function onPeriodToggleUpdate(next: string): void {
    setUpdatesPeriodDaysFromToggle(next, props.updates_period_days);
}
</script>

<template>
    <Head>
        <title>{{ t('family.updates.title') }}</title>
    </Head>

    <FamilyLayout>
        <div class="flex min-w-0 flex-col gap-6">
            <div class="space-y-2">
                <h1 class="text-2xl font-semibold text-text-heading">
                    {{ t('family.updates.heading') }}
                </h1>
                <ActivePatientBadge :family="props.family" />
            </div>

            <SegmentedToggle
                v-if="props.family.has_linked_patient"
                :model-value="periodToggleValue"
                :options="[
                    {
                        value: '1',
                        label: t('family.updates.periodToggle.oneDay'),
                    },
                    {
                        value: '3',
                        label: t('family.updates.periodToggle.threeDays'),
                    },
                    {
                        value: '5',
                        label: t('family.updates.periodToggle.fiveDays'),
                    },
                ]"
                @update:model-value="onPeriodToggleUpdate"
            />

            <p
                v-if="!props.family.has_linked_patient"
                class="max-w-prose text-sm leading-relaxed text-text-muted"
            >
                {{ t('family.updates.notLinked') }}
            </p>

            <p
                v-else-if="!hasUpdates"
                class="max-w-prose text-sm leading-relaxed text-text-muted"
            >
                {{ t('family.updates.emptyInPeriod') }}
            </p>

            <div
                v-else
                class="flex min-w-0 flex-col gap-8"
            >
                <section
                    v-if="props.updates_checkins.length > 0"
                    class="space-y-4"
                >
                    <h2
                        v-if="showSectionHeadings"
                        class="text-lg font-semibold text-text-heading"
                    >
                        {{ t('family.updates.wellbeingHeading') }}
                    </h2>
                    <FamilyWellbeingCheckinCard
                        v-for="checkin in props.updates_checkins"
                        :key="checkin.id"
                        :checkin="checkin"
                    />
                </section>

                <section
                    v-if="props.updates_medication_slots.length > 0"
                    class="space-y-4"
                >
                    <h2
                        v-if="showSectionHeadings"
                        class="text-lg font-semibold text-text-heading"
                    >
                        {{ t('family.updates.medicationsHeading') }}
                    </h2>
                    <FamilyUpdatesMedicationDayGroup
                        v-for="group in medicationSlotsByDate"
                        :key="group.date"
                        :group="group"
                    />
                </section>
            </div>
        </div>
    </FamilyLayout>
</template>
