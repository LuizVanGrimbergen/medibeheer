<script setup lang="ts">
import { Calendar, Clock, FileText, Package, Pencil, Pill, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Card, CardContent } from '@/Components/ui/card';
import { IconActionButton } from '@/Components/ui/icon-action-button';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import type { MedicationListItem, MedicationTypeValue } from '@/lib/types';

const props = defineProps<{
    medication: MedicationListItem;
}>();

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
        endTrimmed.length < 1 ? '—' : formatYmdForMedicationCard(endTrimmed);

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
            const part = segment.trim();

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

const doseLine = computed((): string | null => {
    const dose = props.medication.dose?.trim();

    if (dose === undefined || dose.length < 1) {
        return null;
    }

    const unit = props.medication.dose_unit;

    if (unit === null) {
        return dose;
    }

    const chip = medicationDoseUnitChipForAmount(t, dose, unit);

    return `${dose} ${chip}`;
});

const typeLabel = computed(() =>
    t(`patient.medications.types.${props.medication.type_medication as MedicationTypeValue}`),
);

const notePreview = computed((): string | null => {
    const raw = props.medication.note;

    if (raw === null) {
        return null;
    }

    const trimmed = raw.trim();

    if (trimmed.length < 1) {
        return null;
    }

    return trimmed;
});
</script>

<template>
    <Card
        class="min-w-0 w-full rounded-3xl border border-border/80 bg-surface text-text shadow-md shadow-black/[0.04]"
    >
        <CardContent class="relative p-6 sm:p-7">
            <div
                class="absolute right-6 top-6 z-10 flex flex-row items-center gap-0.5 sm:right-7 sm:top-7"
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

            <div class="flex min-w-0 flex-1 items-start gap-4 pr-21 sm:pr-28">
                <div
                    class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/12"
                >
                    <span class="sr-only">{{ typeLabel }}</span>
                    <Pill
                        class="size-6 shrink-0 text-primary"
                        aria-hidden="true"
                    />
                </div>
                <div class="min-w-0 flex-1 overflow-hidden space-y-3.5">
                    <p
                        class="truncate text-lg font-bold leading-snug text-text-heading sm:text-xl"
                    >
                        {{ medication.name }}
                    </p>
                    <p
                        v-if="doseLine !== null"
                        class="flex min-w-0 items-start gap-3 text-sm leading-relaxed text-text-muted sm:text-base"
                    >
                        <Package
                            class="mt-0.5 size-5 shrink-0 text-text-muted"
                            aria-hidden="true"
                        />
                        <span class="min-w-0 flex-1">
                            <span
                                class="mb-1 block text-sm font-semibold leading-snug text-text-heading sm:text-base"
                            >
                                {{ t('patient.medications.overview.amountPerIntake') }}
                            </span>
                            <span
                                class="block text-base tabular-nums text-text sm:text-lg"
                            >
                                {{ doseLine }}
                            </span>
                        </span>
                    </p>
                    <p
                        v-if="sortedDoseTimes.length > 0"
                        class="flex min-w-0 items-start gap-3 text-sm leading-relaxed text-text-muted sm:text-base"
                    >
                        <Clock
                            class="mt-0.5 size-5 shrink-0 text-text-muted"
                            aria-hidden="true"
                        />
                        <span class="min-w-0 flex-1">
                            <span
                                class="mb-1 block text-sm font-semibold leading-snug text-text-heading sm:text-base"
                            >
                                {{ t('patient.medications.fields.doseTime') }}
                            </span>
                            <span class="block space-y-1">
                                <span
                                    v-for="time in sortedDoseTimes"
                                    :key="time"
                                    class="block text-base tabular-nums text-text sm:text-lg"
                                >
                                    {{ time }}
                                </span>
                            </span>
                        </span>
                    </p>
                    <p
                        v-if="scheduleDateRow !== null"
                        class="flex min-w-0 items-start gap-3 text-sm leading-relaxed text-text-muted sm:text-base"
                        :aria-label="scheduleDateRow.ariaLabel"
                    >
                        <Calendar
                            class="mt-0.5 size-5 shrink-0 text-text-muted"
                            aria-hidden="true"
                        />
                        <span class="grid min-w-0 flex-1 grid-cols-1 gap-3.5">
                            <span class="block min-w-0">
                                <span
                                    class="mb-1 block text-sm font-semibold leading-snug text-text-heading sm:text-base"
                                >
                                    {{ t('patient.medications.fields.startDate') }}
                                </span>
                                <time
                                    v-if="scheduleDateRow.startIso !== null"
                                    class="block text-base tabular-nums text-text sm:text-lg"
                                    :datetime="scheduleDateRow.startIso"
                                >
                                    {{ scheduleDateRow.startDisplay }}
                                </time>
                                <span
                                    v-else
                                    class="block text-base tabular-nums text-text sm:text-lg"
                                >
                                    {{ scheduleDateRow.startDisplay }}
                                </span>
                            </span>
                            <span class="block min-w-0">
                                <span
                                    class="mb-1 block text-sm font-semibold leading-snug text-text-heading sm:text-base"
                                >
                                    {{ t('patient.medications.fields.endDate') }}
                                </span>
                                <time
                                    v-if="scheduleDateRow.endIso !== null"
                                    class="block text-base tabular-nums text-text sm:text-lg"
                                    :datetime="scheduleDateRow.endIso"
                                >
                                    {{ scheduleDateRow.endDisplay }}
                                </time>
                                <span
                                    v-else
                                    class="block text-base tabular-nums text-text sm:text-lg"
                                >
                                    {{ scheduleDateRow.endDisplay }}
                                </span>
                            </span>
                        </span>
                    </p>
                    <p
                        v-if="notePreview !== null"
                        class="flex min-w-0 items-start gap-3 text-sm leading-relaxed text-text-muted sm:text-base"
                    >
                        <FileText
                            class="mt-0.5 size-5 shrink-0 text-text-muted"
                            aria-hidden="true"
                        />
                        <span class="min-w-0 flex-1">
                            <span
                                class="mb-1 block text-sm font-semibold leading-snug text-text-heading sm:text-base"
                            >
                                {{ t('patient.medications.fields.note') }}
                            </span>
                            <span
                                class="line-clamp-4 block whitespace-pre-wrap wrap-break-word text-base text-text sm:text-lg"
                            >
                                {{ notePreview }}
                            </span>
                        </span>
                    </p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
