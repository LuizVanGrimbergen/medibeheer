<script setup lang="ts">
import { Calendar, Clock, FileText, Package, Pencil, Scale, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationStockControls from '@/Components/Medications/MedicationStockControls.vue';
import MedicationTypeLeadIcon from '@/Components/Medications/MedicationTypeLeadIcon.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { IconActionButton } from '@/Components/ui/icon-action-button';
import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationListVisualToneClasses } from '@/lib/patient/inventory/medicationListVisualToneClasses';
import {
    medicationIntakeDoseLine,
    medicationIntakeNotePreview,
    medicationTypeLabel,
} from '@/lib/patient/medications/display/medicationIntakeSlotDisplay';
import type { MedicationListItem } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        medication: MedicationListItem;
        showActions?: boolean;
        showStock?: boolean;
        stockUpdateRouteName?: string;
    }>(),
    {
        showActions: true,
        showStock: false,
        stockUpdateRouteName: 'patient.medications.stocks.update',
    },
);

const emit = defineEmits<{
    edit: [];
    delete: [];
}>();

const { t, locale } = useI18n();

const formatYmdForMedicationCard = (ymd: string): string => {
    const parts = ymd.split('-').map(Number);
    const y = parts[0];
    const m = parts[1];
    const d = parts[2];

    if (
        y === undefined
        || m === undefined
        || d === undefined
        || Number.isNaN(y)
        || Number.isNaN(m)
        || Number.isNaN(d)
    ) {
        return ymd;
    }

    const date = new Date(y, m - 1, d);
    const localeTag = locale.value === 'nl' ? 'nl-NL' : undefined;

    return new Intl.DateTimeFormat(localeTag, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(date);
};

const scheduleDateRow = computed((): {
    startDisplay: string;
    endDisplay: string;
    startIso: string | null;
    endIso: string | null;
    ariaLabel: string;
} | null => {
    const first = props.medication.schedules[0];

    if (first === undefined) {
        return null;
    }

    const startTrimmed = first.start_date?.trim() ?? '';
    const endTrimmed = first.end_date?.trim() ?? '';

    if (startTrimmed.length < 1 && endTrimmed.length < 1) {
        return null;
    }

    const startDisplay =
        startTrimmed.length < 1 ? '—' : formatYmdForMedicationCard(startTrimmed);
    const endDisplay =
        endTrimmed.length < 1
            ? t('patient.medications.intakePeriodPresets.ongoing')
            : formatYmdForMedicationCard(endTrimmed);

    return {
        startDisplay,
        endDisplay,
        startIso: startTrimmed.length < 1 ? null : startTrimmed,
        endIso: endTrimmed.length < 1 ? null : endTrimmed,
        ariaLabel: t('patient.medications.cardIntakePeriodAria', {
            start: startDisplay,
            end: endDisplay,
        }),
    };
});

const minutesSinceMidnight = (value: string): number | null => {
    const match = /^(\d{1,2}):(\d{2})$/.exec(value.trim());

    if (match === null) {
        return null;
    }

    const hours = Number(match[1]);
    const minutes = Number(match[2]);

    if (
        Number.isNaN(hours)
        || Number.isNaN(minutes)
        || hours > 23
        || minutes > 59
    ) {
        return null;
    }

    return hours * 60 + minutes;
};

const sortedDoseTimes = computed((): string[] => {
    const seen = new Set<string>();

    for (const schedule of props.medication.schedules) {
        const raw = schedule.dose_time?.trim();

        if (raw === undefined || raw.length < 1) {
            continue;
        }

        for (const segment of raw.split(',')) {
            const part = segment.trim().split('|', 1)[0]?.trim() ?? '';

            if (part.length > 0) {
                seen.add(part);
            }
        }
    }

    return Array.from(seen).sort((a, b) => {
        const left = minutesSinceMidnight(a);
        const right = minutesSinceMidnight(b);

        if (left === null && right === null) {
            return a.localeCompare(b);
        }

        if (left === null) {
            return 1;
        }

        if (right === null) {
            return -1;
        }

        return left - right;
    });
});

const doseLine = computed(() =>
    medicationIntakeDoseLine(t, {
        dose: props.medication.dose,
        dose_unit: props.medication.dose_unit,
        note: props.medication.note,
        type_medication: props.medication.type_medication,
    }),
);

const strengthLine = computed(() => props.medication.strength?.trim() || null);

const typeLabel = computed(() => medicationTypeLabel(t, props.medication.type_medication));

const stockProgressTone = computed(() => medicationListVisualTone(props.medication));

const medicationVisualToneClasses = computed(() =>
    medicationListVisualToneClasses(stockProgressTone.value),
);

const medicationPillWrapToneClass = computed(
    () => medicationVisualToneClasses.value.pillWrap,
);

const medicationPillIconClass = computed(
    () => medicationVisualToneClasses.value.pillIcon,
);

const notePreview = computed(() => medicationIntakeNotePreview(props.medication));
</script>

<template>
    <Card
        class="min-w-0 w-full rounded-3xl border-2 bg-surface text-text shadow-md shadow-black/[0.04]"
        :class="medicationVisualToneClasses.border"
    >
        <CardContent class="relative p-4 pb-6 pt-5 sm:p-8">
            <div
                v-if="props.showActions"
                class="absolute right-4 top-4 z-10 flex flex-row items-center gap-0.5 sm:right-8 sm:top-8"
                role="toolbar"
                :aria-label="t('patient.medications.cardActionsAriaLabel')"
            >
                <IconActionButton
                    :ariaLabel="t('patient.medications.actions.edit')"
                    @click="emit('edit')"
                >
                    <Pencil
                        class="size-5"
                        aria-hidden="true"
                    />
                </IconActionButton>
                <IconActionButton
                    tone="danger"
                    :ariaLabel="t('patient.medications.actions.delete')"
                    @click="emit('delete')"
                >
                    <Trash2
                        class="size-5"
                        aria-hidden="true"
                    />
                </IconActionButton>
            </div>

            <div
                class="flex min-w-0 w-full flex-col gap-5 sm:flex-row sm:items-start sm:gap-6"
                :class="{ 'pr-21 sm:pr-28': props.showActions }"
            >
                <div
                    class="flex size-12 shrink-0 items-center justify-center rounded-2xl sm:size-16"
                    :class="medicationPillWrapToneClass"
                >
                    <span class="sr-only">{{ typeLabel }}</span>
                    <MedicationTypeLeadIcon
                        :medication-type="medication.type_medication"
                        :icon-tone-class="medicationPillIconClass"
                    />
                </div>
                <div
                    class="flex w-full min-w-0 flex-col space-y-3.5 sm:flex-1 sm:items-stretch"
                >
                    <p
                        class="min-w-0 wrap-break-word text-lg font-bold leading-snug text-text-heading sm:text-xl"
                    >
                        {{ medication.name }}
                    </p>
                    <p
                        v-if="doseLine !== null"
                        class="flex min-w-0 items-start gap-3 text-sm leading-relaxed text-text-muted sm:items-center sm:gap-3 sm:text-base"
                    >
                        <Package
                            class="mt-0.5 size-5 shrink-0 text-text-muted sm:mt-0"
                            aria-hidden="true"
                        />
                        <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                            <span
                                class="block text-sm font-semibold leading-snug text-text-muted sm:text-base"
                            >
                                {{ t('patient.medications.overview.amountPerIntake') }}
                            </span>
                            <span
                                class="block text-base font-semibold tabular-nums leading-relaxed text-text sm:text-lg"
                            >
                                {{ doseLine }}
                            </span>
                        </span>
                    </p>
                    <p
                        v-if="strengthLine !== null"
                        class="flex min-w-0 items-start gap-3 text-sm leading-relaxed text-text-muted sm:items-center sm:gap-3 sm:text-base"
                    >
                        <Scale
                            class="mt-0.5 size-5 shrink-0 text-text-muted sm:mt-0"
                            aria-hidden="true"
                        />
                        <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                            <span
                                class="block text-sm font-semibold leading-snug text-text-muted sm:text-base"
                            >
                                {{ t('patient.medications.fields.strength') }}
                            </span>
                            <span
                                class="block text-base font-semibold leading-relaxed text-text sm:text-lg"
                            >
                                {{ strengthLine }}
                            </span>
                        </span>
                    </p>
                    <p
                        v-if="sortedDoseTimes.length > 0"
                        class="flex min-w-0 items-start gap-3 text-sm leading-relaxed text-text-muted sm:items-center sm:gap-3 sm:text-base"
                    >
                        <Clock
                            class="mt-0.5 size-5 shrink-0 self-start text-text-muted sm:mt-0"
                            aria-hidden="true"
                        />
                        <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                            <span
                                class="block text-sm font-semibold leading-snug text-text-muted sm:text-base"
                            >
                                {{ t('patient.medications.fields.doseTime') }}
                            </span>
                            <span class="flex flex-col gap-1">
                                <span
                                    v-for="time in sortedDoseTimes"
                                    :key="time"
                                    class="block text-base font-semibold tabular-nums leading-relaxed text-text sm:text-lg"
                                >
                                    {{ time }}
                                </span>
                            </span>
                        </span>
                    </p>
                    <p
                        v-if="scheduleDateRow !== null"
                        class="flex min-w-0 items-start gap-3 text-sm leading-relaxed text-text-muted sm:items-center sm:gap-3 sm:text-base"
                        :aria-label="scheduleDateRow.ariaLabel"
                    >
                        <Calendar
                            class="mt-0.5 size-5 shrink-0 self-start text-text-muted sm:mt-0"
                            aria-hidden="true"
                        />
                        <span class="grid min-w-0 flex-1 grid-cols-1 gap-3.5">
                            <span class="flex min-w-0 flex-col gap-0.5">
                                <span
                                    class="block text-sm font-semibold leading-snug text-text-muted sm:text-base"
                                >
                                    {{ t('patient.medications.fields.startDate') }}
                                </span>
                                <time
                                    v-if="scheduleDateRow.startIso !== null"
                                    class="block text-base font-semibold tabular-nums leading-relaxed text-text sm:text-lg"
                                    :datetime="scheduleDateRow.startIso"
                                >
                                    {{ scheduleDateRow.startDisplay }}
                                </time>
                                <span
                                    v-else
                                    class="block text-base font-semibold tabular-nums leading-relaxed text-text sm:text-lg"
                                >
                                    {{ scheduleDateRow.startDisplay }}
                                </span>
                            </span>
                            <span class="flex min-w-0 flex-col gap-0.5">
                                <span
                                    class="block text-sm font-semibold leading-snug text-text-muted sm:text-base"
                                >
                                    {{ t('patient.medications.fields.endDate') }}
                                </span>
                                <time
                                    v-if="scheduleDateRow.endIso !== null"
                                    class="block text-base font-semibold tabular-nums leading-relaxed text-text sm:text-lg"
                                    :datetime="scheduleDateRow.endIso"
                                >
                                    {{ scheduleDateRow.endDisplay }}
                                </time>
                                <span
                                    v-else
                                    class="block text-base font-semibold tabular-nums leading-relaxed text-text sm:text-lg"
                                >
                                    {{ scheduleDateRow.endDisplay }}
                                </span>
                            </span>
                        </span>
                    </p>
                    <p
                        v-if="notePreview !== null"
                        class="flex min-w-0 items-start gap-3 text-sm leading-relaxed text-text-muted sm:items-center sm:gap-3 sm:text-base"
                    >
                        <FileText
                            class="mt-0.5 size-5 shrink-0 self-start text-text-muted sm:mt-0"
                            aria-hidden="true"
                        />
                        <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                            <span
                                class="block text-sm font-semibold leading-snug text-text-muted sm:text-base"
                            >
                                {{ t('patient.medications.fields.note') }}
                            </span>
                            <span
                                class="line-clamp-4 block whitespace-pre-wrap wrap-break-word text-base font-semibold leading-relaxed text-text sm:text-lg"
                            >
                                {{ notePreview }}
                            </span>
                        </span>
                    </p>

                    <MedicationStockControls
                        v-if="props.showStock"
                        :medication="medication"
                        :update-route-name="props.stockUpdateRouteName"
                        :id-prefix="`medication-card-stock-${medication.id}`"
                        class="border-t border-border/70 pt-4"
                    />
                </div>
            </div>
        </CardContent>
    </Card>
</template>
