<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';
import { Moon, Sun, Sunrise, Sunset } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import TodayMedicationIntakeCard from '@/Components/Patient/Medications/TodayMedicationIntakeCard.vue';
import { partitionTodayMedicationIntakes } from '@/lib/patient/medications/todayMedicationIntakeDayPeriod';
import type {
    TodayMedicationIntakeDayPeriodValue,
    TodayMedicationIntakeSlot,
} from '@/lib/types';

const periodIcons: Record<TodayMedicationIntakeDayPeriodValue, LucideIcon> = {
    morning: Sunrise,
    afternoon: Sun,
    evening: Sunset,
    night: Moon,
};

const props = withDefaults(
    defineProps<{
        slots?: TodayMedicationIntakeSlot[];
    }>(),
    {
        slots: () => [],
    },
);

const { t } = useI18n();

const intakeGroups = computed(() =>
    partitionTodayMedicationIntakes(props.slots),
);

const periodGroups = computed(() => intakeGroups.value.periodGroups);

const takenSlots = computed(() => intakeGroups.value.takenSlots);

function slotKey(slot: TodayMedicationIntakeSlot): string {
    return `${slot.medication_schedule_id}-${slot.dose_time}`;
}

function periodTitle(period: TodayMedicationIntakeDayPeriodValue): string {
    return t(`patient.dashboard.todayMedications.periods.${period}.title`);
}

function periodHint(period: TodayMedicationIntakeDayPeriodValue): string {
    return t(`patient.dashboard.todayMedications.periods.${period}.hint`);
}

function periodIcon(period: TodayMedicationIntakeDayPeriodValue): LucideIcon {
    return periodIcons[period];
}
</script>

<template>
    <section
        v-if="props.slots.length > 0"
        class="space-y-3 sm:space-y-4"
        :aria-label="t('patient.dashboard.todayMedications.title')"
    >
        <div class="flex flex-col gap-6 sm:gap-8">
            <section
                v-for="group in periodGroups"
                :key="group.period"
                class="space-y-3 sm:space-y-4"
                :aria-label="periodTitle(group.period)"
            >
                <div class="flex items-start gap-2.5 px-0.5 sm:gap-3">
                    <component
                        :is="periodIcon(group.period)"
                        class="text-primary mt-0.5 size-6 shrink-0 sm:mt-0 sm:size-7"
                        aria-hidden="true"
                    />
                    <div class="min-w-0 space-y-1">
                        <h3
                            class="text-text-heading text-lg font-bold sm:text-xl"
                        >
                            {{ periodTitle(group.period) }}
                        </h3>
                        <p class="text-text-muted text-base sm:text-lg">
                            {{ periodHint(group.period) }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-4 sm:gap-5">
                    <TodayMedicationIntakeCard
                        v-for="slot in group.slots"
                        :key="slotKey(slot)"
                        :intake-slot="slot"
                    />
                </div>
            </section>

            <section
                v-if="takenSlots.length > 0"
                class="border-border/70 space-y-3 border-t pt-6 sm:space-y-4 sm:pt-8"
                :aria-label="
                    t('patient.dashboard.todayMedications.takenSection.title')
                "
            >
                <h3 class="text-text-muted px-0.5 text-lg font-bold sm:text-xl">
                    {{
                        t(
                            'patient.dashboard.todayMedications.takenSection.title',
                        )
                    }}
                </h3>

                <div class="flex flex-col gap-4 sm:gap-5">
                    <TodayMedicationIntakeCard
                        v-for="slot in takenSlots"
                        :key="slotKey(slot)"
                        :intake-slot="slot"
                    />
                </div>
            </section>
        </div>
    </section>
</template>
