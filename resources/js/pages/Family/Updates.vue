<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import FamilyUpdatesEchoListener from '@/Components/Family/Updates/FamilyUpdatesEchoListener.vue';
import FamilyUpdatesMedicationDayGroup from '@/Components/Family/Updates/FamilyUpdatesMedicationDayGroup.vue';
import FamilyWellbeingCheckinCard from '@/Components/Family/Wellbeing/FamilyWellbeingCheckinCard.vue';
import { SegmentedToggle } from '@/Components/ui/segmented-toggle';
import { setUpdatesPeriodDaysFromToggle } from '@/composables/useFamilyUpdatesActions';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import { groupMedicationIntakesByDate } from '@/lib/family/medications/groupMedicationIntakesByDate';
import type { FamilyUpdatesScreenProps } from '@/lib/family/updates/familyUpdatesScreenProps';
import type { FamilyDashboardProps } from '@/lib/types';

const props = defineProps<
    FamilyUpdatesScreenProps & {
        family: FamilyDashboardProps;
    }
>();

const { t } = useI18n();

const periodToggleValue = computed((): string => String(props.updates_period_days));

const activePatientId = computed((): number | null => props.family.active_patient_id);

const hasCheckins = computed((): boolean => props.updates_checkins.length > 0);

const hasMedicationIntakes = computed(
    (): boolean => props.updates_medication_intakes.length > 0,
);

const hasUpdates = computed(
    (): boolean => hasCheckins.value || hasMedicationIntakes.value,
);

const medicationIntakesByDate = computed(() =>
    groupMedicationIntakesByDate(props.updates_medication_intakes),
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
        <FamilyUpdatesEchoListener
            v-if="props.family.has_linked_patient && activePatientId !== null"
            :key="activePatientId"
            :patient-id="activePatientId"
        />

        <FamilyPageShell
            :title="t('family.updates.heading')"
            :family="props.family"
            :show-active-patient="props.family.has_linked_patient"
        >
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

            <template v-else>
                <section
                    v-if="hasCheckins"
                    class="space-y-4"
                >
                    <h2 class="text-lg font-semibold text-text-heading md:text-base">
                        {{ t('family.updates.wellbeingHeading') }}
                    </h2>

                    <FamilyWellbeingCheckinCard
                        v-for="checkin in props.updates_checkins"
                        :key="checkin.id"
                        :checkin="checkin"
                    />
                </section>

                <section
                    v-if="hasMedicationIntakes"
                    class="space-y-4"
                >
                    <h2 class="text-lg font-semibold text-text-heading md:text-base">
                        {{ t('family.updates.medicationsHeading') }}
                    </h2>

                    <FamilyUpdatesMedicationDayGroup
                        v-for="group in medicationIntakesByDate"
                        :key="group.date"
                        :date="group.date"
                        :intakes="group.intakes"
                    />
                </section>
            </template>
        </FamilyPageShell>
    </FamilyLayout>
</template>
