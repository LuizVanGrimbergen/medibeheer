<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationTypeLeadIcon from '@/Components/Medications/MedicationTypeLeadIcon.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import type { MedicationTypeValue } from '@/lib/types';

const props = defineProps<{
    slot: MedicationIntakeHistorySlot;
}>();

const { t } = useI18n();

const isTaken = computed(() => props.slot.taken_at !== null);

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
</script>

<template>
    <Card class="min-w-0 w-full rounded-2xl border border-border bg-surface text-text shadow-sm">
        <CardContent class="flex flex-col gap-4 p-5 sm:gap-5 sm:p-6">
            <div class="flex min-w-0 items-start gap-4">
                <div
                    class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-primary/10 sm:size-14"
                >
                    <MedicationTypeLeadIcon
                        :medication-type="slot.type_medication"
                        icon-tone-class="text-primary"
                    />
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="wrap-break-word text-lg font-bold leading-snug text-text-heading">
                        {{ slot.name }}
                    </h4>
                    <p class="mt-1 text-sm font-medium text-text-muted">
                        {{ typeLabel }}
                    </p>
                </div>
                <span
                    class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold"
                    :class="
                        isTaken
                            ? 'bg-success/12 text-success'
                            : 'bg-surface-hover text-text-muted'
                    "
                >
                    <Check
                        v-if="isTaken"
                        class="size-3.5 shrink-0"
                        aria-hidden="true"
                    />
                    {{
                        isTaken
                            ? t('patient.medications.history.slot.taken')
                            : t('patient.medications.history.slot.notTaken')
                    }}
                </span>
            </div>

            <div
                class="grid min-w-0 gap-3"
                :class="doseLine !== null ? 'grid-cols-2' : 'grid-cols-1'"
            >
                <div
                    v-if="doseLine !== null"
                    class="flex min-w-0 flex-col gap-1 rounded-xl border border-border/70 bg-bg px-3 py-2.5"
                >
                    <span class="text-xs font-semibold text-text-muted">
                        {{ t('patient.dashboard.todayMedications.intakeCard.dose') }}
                    </span>
                    <span class="text-base font-bold tabular-nums text-text-heading">
                        {{ doseLine }}
                    </span>
                </div>
                <div class="flex min-w-0 flex-col gap-1 rounded-xl border border-border/70 bg-bg px-3 py-2.5">
                    <span class="text-xs font-semibold text-text-muted">
                        {{ t('patient.dashboard.todayMedications.intakeCard.time') }}
                    </span>
                    <span class="text-base font-bold tabular-nums text-text-heading">
                        {{ slot.dose_time }}
                    </span>
                </div>
            </div>

            <p
                v-if="notePreview !== null"
                class="min-w-0 whitespace-pre-wrap wrap-break-word text-sm leading-relaxed text-text"
            >
                <span class="font-semibold text-text-muted">
                    {{ t('patient.dashboard.todayMedications.intakeCard.note') }}:
                </span>
                {{ notePreview }}
            </p>
        </CardContent>
    </Card>
</template>
