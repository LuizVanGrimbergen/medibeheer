<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { AlertTriangle, Check } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationTypeLeadIcon from '@/Components/Medications/MedicationTypeLeadIcon.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { medicationVisualToneFromContext } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationListVisualToneClasses } from '@/lib/patient/inventory/medicationListVisualToneClasses';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import type { MedicationTypeValue, TodayMedicationIntakeSlot } from '@/lib/types';

const props = defineProps<{
    slot: TodayMedicationIntakeSlot;
}>();

const { t } = useI18n();

const isTaken = computed(() => props.slot.taken_at !== null);

const form = useForm<{
    medication_schedule_id: number;
    dose_time: string;
}>({
    medication_schedule_id: props.slot.medication_schedule_id,
    dose_time: props.slot.dose_time,
});

const doseLine = computed((): string | null => {
    const dose = props.slot.dose?.trim();

    if (dose === undefined || dose === null || dose.length < 1) {
        return null;
    }

    const unit = props.slot.dose_unit;

    if (unit === null) {
        return dose;
    }

    const chip = medicationDoseUnitChipForAmount(t, dose, unit);

    return `${dose} ${chip}`;
});

const notePreview = computed((): string | null => {
    const raw = props.slot.note;

    if (raw === null) {
        return null;
    }

    const trimmed = raw.trim();

    if (trimmed.length < 1) {
        return null;
    }

    return trimmed;
});

const typeLabel = computed(() =>
    t(`patient.medications.types.${props.slot.type_medication as MedicationTypeValue}`),
);

const stockProgressTone = computed(() =>
    medicationVisualToneFromContext({
        supply_estimate_days: props.slot.supply_estimate_days,
        supply_estimate_quality: props.slot.supply_estimate_quality,
    }),
);

const isCriticalSupply = computed(() => stockProgressTone.value === 'critical');

const intakeCardToneClasses = computed(() =>
    isCriticalSupply.value
        ? medicationListVisualToneClasses('critical')
        : medicationListVisualToneClasses(null),
);

const showCriticalSupplyAlert = computed(() => isCriticalSupply.value);

const markTakenAriaLabel = computed(() =>
    t('patient.dashboard.todayMedications.markTakenAria', {
        name: props.slot.name,
        time: props.slot.dose_time,
    }),
);

function markTaken(): void {
    if (isTaken.value || form.processing) {
        return;
    }

    form.post(route('patient.medication-intakes.store'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Card
        class="min-w-0 w-full rounded-3xl border-2 bg-surface text-text shadow-md shadow-black/[0.04]"
        :class="intakeCardToneClasses.border"
    >
        <CardContent class="relative flex flex-col gap-5 p-5 sm:gap-6 sm:p-6">
            <div
                v-if="showCriticalSupplyAlert"
                class="pointer-events-none absolute right-4 top-4 z-10 sm:right-6 sm:top-6"
                role="img"
                :aria-label="t('patient.inventory.lowStockBadge')"
            >
                <AlertTriangle
                    class="size-6 shrink-0 animate-supply-alert-flicker text-danger sm:size-7"
                    aria-hidden="true"
                />
            </div>

            <div
                class="flex min-w-0 items-start gap-4"
                :class="showCriticalSupplyAlert ? 'pr-8 sm:pr-10' : null"
            >
                <div
                    class="flex size-14 shrink-0 items-center justify-center rounded-2xl sm:size-16"
                    :class="intakeCardToneClasses.pillWrap"
                >
                    <span class="sr-only">{{ typeLabel }}</span>
                    <MedicationTypeLeadIcon
                        :medication-type="slot.type_medication"
                        :icon-tone-class="intakeCardToneClasses.pillIcon"
                    />
                </div>

                <div class="min-w-0 flex-1">
                    <h4
                        class="wrap-break-word text-xl font-bold leading-snug text-text-heading sm:text-2xl"
                    >
                        {{ slot.name }}
                    </h4>
                    <p class="mt-1 text-base font-medium leading-snug text-text-muted sm:text-lg">
                        {{ typeLabel }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-4 sm:gap-5">
                <div
                    class="grid min-w-0 gap-3 sm:gap-4"
                    :class="doseLine !== null ? 'grid-cols-2' : 'grid-cols-1'"
                >
                    <div
                        v-if="doseLine !== null"
                        class="flex min-w-0 flex-col justify-center gap-1.5 rounded-2xl border border-border/70 bg-bg px-4 py-3.5 sm:gap-2 sm:px-5 sm:py-4"
                    >
                        <span class="text-sm font-semibold leading-snug text-text-muted sm:text-base">
                            {{ t('patient.dashboard.todayMedications.intakeCard.dose') }}
                        </span>
                        <span
                            class="wrap-break-word text-xl font-bold tabular-nums leading-tight text-text-heading sm:text-2xl"
                        >
                            {{ doseLine }}
                        </span>
                    </div>

                    <div
                        class="flex min-w-0 flex-col justify-center gap-1.5 rounded-2xl border border-border/70 bg-bg px-4 py-3.5 sm:gap-2 sm:px-5 sm:py-4"
                    >
                        <span class="text-sm font-semibold leading-snug text-text-muted sm:text-base">
                            {{ t('patient.dashboard.todayMedications.intakeCard.time') }}
                        </span>
                        <span
                            class="text-xl font-bold tabular-nums leading-tight text-text-heading sm:text-2xl"
                        >
                            {{ slot.dose_time }}
                        </span>
                    </div>
                </div>

                <div
                    v-if="notePreview !== null"
                    class="flex flex-col gap-1.5"
                >
                    <span class="text-sm font-semibold text-text-muted sm:text-base">
                        {{ t('patient.dashboard.todayMedications.intakeCard.note') }}
                    </span>
                    <p
                        class="min-w-0 whitespace-pre-wrap wrap-break-word text-base leading-relaxed text-text sm:text-lg"
                    >
                        {{ notePreview }}
                    </p>
                </div>
            </div>

            <Button
                type="button"
                class="min-h-14 w-full touch-manipulation rounded-2xl text-lg font-bold sm:min-h-12 sm:text-base"
                :class="
                    isTaken
                        ? 'border-2 border-success bg-success/10 text-text-heading hover:bg-success/10'
                        : undefined
                "
                :variant="isTaken ? 'outline' : 'default'"
                :disabled="isTaken || form.processing"
                :aria-label="isTaken ? undefined : markTakenAriaLabel"
                :aria-pressed="isTaken"
                @click="markTaken"
            >
                <Check
                    v-if="isTaken"
                    class="size-6 shrink-0 text-success sm:size-5"
                    aria-hidden="true"
                />
                {{
                    isTaken
                        ? t('patient.dashboard.todayMedications.taken')
                        : t('patient.dashboard.todayMedications.markTaken')
                }}
            </Button>
        </CardContent>
    </Card>
</template>
