<script setup lang="ts">
import { Calendar, Clock, Package, Pencil, Scale, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationStockControls from '@/Components/Medications/MedicationStockControls.vue';
import MedicationTypeLeadIcon from '@/Components/Medications/MedicationTypeLeadIcon.vue';
import PatientListCardDetailRow from '@/Components/Patient/PatientListCardDetailRow.vue';
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
        listStatusEndedLabelKey?: string;
        listStatusRemovedLabelKey?: string;
    }>(),
    {
        showActions: true,
        showStock: false,
        stockUpdateRouteName: 'patient.medications.stocks.update',
        listStatusEndedLabelKey: 'patient.medications.listStatus.ended',
        listStatusRemovedLabelKey: 'patient.medications.listStatus.removed',
    },
);

const emit = defineEmits<{
    edit: [];
    delete: [];
}>();

const { t, locale } = useI18n();

const isInactiveListItem = computed(
    () =>
        props.medication.list_status === 'ended'
        || props.medication.list_status === 'removed',
);

const listStatusLabel = computed((): string | null => {
    if (props.medication.list_status === 'ended') {
        return t(props.listStatusEndedLabelKey);
    }

    if (props.medication.list_status === 'removed') {
        return t(props.listStatusRemovedLabelKey);
    }

    return null;
});

const canEdit = computed(
    () => props.showActions && props.medication.list_status !== 'removed',
);

const canDelete = computed(
    () => props.showActions && props.medication.list_status === 'active',
);

const showCardToolbar = computed(() => canEdit.value || canDelete.value);

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
        class="min-w-0 w-full rounded-3xl border bg-surface text-text shadow-md shadow-black/[0.04]"
        :class="[
            medicationVisualToneClasses.border,
            isInactiveListItem ? 'opacity-90' : null,
        ]"
    >
        <CardContent class="relative space-y-6 p-6 sm:p-7">
            <div
                v-if="showCardToolbar"
                class="absolute right-6 top-6 z-10 flex flex-row items-center gap-0.5 sm:right-7 sm:top-7"
                role="toolbar"
                :aria-label="t('patient.medications.cardActionsAriaLabel')"
            >
                <IconActionButton
                    v-if="canEdit"
                    :ariaLabel="t('patient.medications.actions.edit')"
                    @click="emit('edit')"
                >
                    <Pencil
                        class="size-5"
                        aria-hidden="true"
                    />
                </IconActionButton>
                <IconActionButton
                    v-if="canDelete"
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
                class="flex min-w-0 items-start gap-4"
                :class="showCardToolbar ? 'pr-21 sm:pr-28' : null"
            >
                    <div
                        class="flex size-12 shrink-0 items-center justify-center rounded-xl"
                        :class="medicationPillWrapToneClass"
                        aria-hidden="true"
                    >
                        <span class="sr-only">{{ typeLabel }}</span>
                        <MedicationTypeLeadIcon
                            :medication-type="medication.type_medication"
                            :icon-tone-class="medicationPillIconClass"
                        />
                    </div>
                    <div class="min-w-0 flex-1 overflow-hidden space-y-1">
                        <p
                            class="text-lg font-bold leading-snug text-text-heading sm:text-xl"
                        >
                            {{ medication.name }}
                        </p>
                        <p class="text-base font-normal leading-snug text-text-muted">
                            {{ typeLabel }}
                        </p>
                        <p
                            v-if="listStatusLabel !== null"
                            class="pt-1 text-sm font-semibold leading-snug text-text-muted"
                        >
                            {{ listStatusLabel }}
                        </p>
                    </div>
            </div>

            <div class="space-y-5">
                <PatientListCardDetailRow
                    v-if="doseLine !== null"
                    :label="t('patient.medications.overview.amountPerIntake')"
                >
                    <template #icon>
                        <Package
                            class="mt-0.5 size-6 shrink-0 text-primary"
                            :stroke-width="2"
                            aria-hidden="true"
                        />
                    </template>
                    {{ doseLine }}
                </PatientListCardDetailRow>

                <PatientListCardDetailRow
                    v-if="strengthLine !== null"
                    :label="t('patient.medications.fields.strength')"
                >
                    <template #icon>
                        <Scale
                            class="mt-0.5 size-6 shrink-0 text-primary"
                            :stroke-width="2"
                            aria-hidden="true"
                        />
                    </template>
                    {{ strengthLine }}
                </PatientListCardDetailRow>

                <div
                    v-if="sortedDoseTimes.length > 0"
                    class="flex gap-4 sm:gap-5"
                >
                    <Clock
                        class="mt-1 size-6 shrink-0 self-start text-primary"
                        :stroke-width="2"
                        aria-hidden="true"
                    />
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <p class="text-base font-semibold leading-tight text-text-heading">
                            {{ t('patient.medications.fields.doseTime') }}
                        </p>
                        <p
                            v-for="time in sortedDoseTimes"
                            :key="time"
                            class="text-lg font-medium leading-relaxed tracking-tight text-text sm:text-xl sm:leading-snug"
                        >
                            {{ time }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="scheduleDateRow !== null"
                    class="flex gap-4 sm:gap-5"
                    :aria-label="scheduleDateRow.ariaLabel"
                >
                    <Calendar
                        class="mt-1 size-6 shrink-0 self-start text-primary"
                        :stroke-width="2"
                        aria-hidden="true"
                    />
                    <div class="min-w-0 flex-1 space-y-3">
                            <div>
                                <p class="text-base font-semibold leading-tight text-text-heading">
                                    {{ t('patient.medications.fields.startDate') }}
                                </p>
                                <time
                                    v-if="scheduleDateRow.startIso !== null"
                                    class="text-lg font-medium leading-relaxed tracking-tight text-text sm:text-xl sm:leading-snug"
                                    :datetime="scheduleDateRow.startIso"
                                >
                                    {{ scheduleDateRow.startDisplay }}
                                </time>
                                <p
                                    v-else
                                    class="text-lg font-medium leading-relaxed tracking-tight text-text sm:text-xl sm:leading-snug"
                                >
                                    {{ scheduleDateRow.startDisplay }}
                                </p>
                            </div>
                            <div>
                                <p class="text-base font-semibold leading-tight text-text-heading">
                                    {{ t('patient.medications.fields.endDate') }}
                                </p>
                                <time
                                    v-if="scheduleDateRow.endIso !== null"
                                    class="text-lg font-medium leading-relaxed tracking-tight text-text sm:text-xl sm:leading-snug"
                                    :datetime="scheduleDateRow.endIso"
                                >
                                    {{ scheduleDateRow.endDisplay }}
                                </time>
                                <p
                                    v-else
                                    class="text-lg font-medium leading-relaxed tracking-tight text-text sm:text-xl sm:leading-snug"
                                >
                                    {{ scheduleDateRow.endDisplay }}
                                </p>
                            </div>
                    </div>
                </div>
            </div>

            <p
                v-if="notePreview !== null"
                class="border-l-[3px] border-primary py-0.5 pl-3.5 text-base italic leading-relaxed text-text sm:text-lg"
            >
                {{ notePreview }}
            </p>

            <MedicationStockControls
                v-if="props.showStock"
                :medication="medication"
                :update-route-name="props.stockUpdateRouteName"
                :id-prefix="`medication-card-stock-${medication.id}`"
                :can-adjust-stock="medication.list_status === 'active'"
                class="border-t border-border/70 pt-5"
            />
        </CardContent>
    </Card>
</template>
