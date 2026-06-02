<script setup lang="ts">
import { Calendar, CalendarClock, Clock, Package, Scale } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationStockControls from '@/Components/Medications/MedicationStockControls.vue';
import MedicationTypeLeadIcon from '@/Components/Medications/MedicationTypeLeadIcon.vue';
import PatientListCardActionsToolbar from '@/Components/Patient/PatientListCardActionsToolbar.vue';
import PatientListCardDetailsGroup from '@/Components/Patient/PatientListCardDetailsGroup.vue';
import PatientListCardDetailsGroupItem from '@/Components/Patient/PatientListCardDetailsGroupItem.vue';
import PatientListCardDetailsToggle from '@/Components/Patient/PatientListCardDetailsToggle.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Collapsible, CollapsibleContent } from '@/Components/ui/collapsible';
import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationListVisualToneClasses } from '@/lib/patient/inventory/medicationListVisualToneClasses';
import {
    medicationCardHeaderSummary,
    medicationIntakeDoseLine,
    medicationIntakeNotePreview,
    medicationTypeLabel,
} from '@/lib/patient/medications/display/medicationIntakeSlotDisplay';
import {
    patientPageCardDetailValueClass,
    patientPageCardHeaderSummaryClass,
    patientPageCardHeaderWithActionsClass,
} from '@/lib/patient/patientPageTypography';
import type { MedicationListItem } from '@/lib/types';

const props = withDefaults(
    defineProps<{
        medication: MedicationListItem;
        showActions?: boolean;
        showStock?: boolean;
        stockUpdateRouteName?: string;
        listStatusEndedLabelKey?: string;
        listStatusRemovedLabelKey?: string;
        defaultOpen?: boolean;
    }>(),
    {
        showActions: true,
        showStock: false,
        stockUpdateRouteName: 'patient.medications.stocks.update',
        listStatusEndedLabelKey: 'patient.medications.listStatus.ended',
        listStatusRemovedLabelKey: 'patient.medications.listStatus.removed',
        defaultOpen: false,
    },
);

const isOpen = ref(props.defaultOpen);

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

const hasEditOrDeleteActions = computed(() => canEdit.value || canDelete.value);

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
    rangeDisplay: string;
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
        rangeDisplay: t('patient.medications.cardIntakePeriodRange', {
            start: startDisplay,
            end: endDisplay,
        }),
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

const doseTimesDisplay = computed(() => sortedDoseTimes.value.join(', '));

const prescriptionExpiryDateRow = computed((): { display: string; iso: string } | null => {
    const trimmed = props.medication.prescription_expiry_date?.trim() ?? '';

    if (trimmed.length < 1) {
        return null;
    }

    return {
        display: formatYmdForMedicationCard(trimmed),
        iso: trimmed,
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

const doseLine = computed(() =>
    medicationIntakeDoseLine(t, {
        dose: props.medication.dose,
        dose_unit: props.medication.dose_unit,
        note: props.medication.note,
        type_medication: props.medication.type_medication,
    }),
);

const strengthLine = computed(() => props.medication.strength?.trim() || null);

const hasMedicationDetailsGroup = computed(
    () =>
        doseLine.value !== null
        || strengthLine.value !== null
        || sortedDoseTimes.value.length > 0
        || scheduleDateRow.value !== null
        || prescriptionExpiryDateRow.value !== null,
);

const typeLabel = computed(() => medicationTypeLabel(t, props.medication.type_medication));

const headerSummary = computed(() =>
    medicationCardHeaderSummary(t, {
        dose: props.medication.dose,
        dose_unit: props.medication.dose_unit,
        note: props.medication.note,
        type_medication: props.medication.type_medication,
        doseTimes: sortedDoseTimes.value,
    }),
);

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
        <CardContent class="relative p-6 sm:p-7">
            <Collapsible v-model:open="isOpen">
                <PatientListCardActionsToolbar
                    v-if="hasEditOrDeleteActions"
                    :ariaLabel="t('patient.medications.cardActionsAriaLabel')"
                    :showEdit="canEdit"
                    :showDelete="canDelete"
                    :editAriaLabel="t('patient.medications.actions.edit')"
                    :deleteAriaLabel="t('patient.medications.actions.delete')"
                    @edit="emit('edit')"
                    @delete="emit('delete')"
                />

                <div
                    class="flex min-w-0 items-start gap-4"
                    :class="
                        hasEditOrDeleteActions ? patientPageCardHeaderWithActionsClass : null
                    "
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
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <p
                            class="text-lg font-bold leading-snug text-text-heading sm:text-xl"
                        >
                            {{ medication.name }}
                        </p>
                        <p
                            v-if="!isOpen"
                            :class="patientPageCardHeaderSummaryClass"
                        >
                            {{ headerSummary }}
                        </p>
                        <p
                            v-if="listStatusLabel !== null"
                            class="text-base font-semibold leading-snug text-text-muted"
                        >
                            {{ listStatusLabel }}
                        </p>
                    </div>
                </div>

                <PatientListCardDetailsToggle
                    v-if="!isOpen"
                    mode="expand"
                    :label="t('patient.medications.cardExpandHint')"
                    :ariaLabel="t('patient.medications.showDetails')"
                />

                <CollapsibleContent>
                    <div class="space-y-6 pt-4">
                        <PatientListCardDetailsGroup v-if="hasMedicationDetailsGroup">
                            <PatientListCardDetailsGroupItem
                                v-if="doseLine !== null"
                                :label="t('patient.medications.overview.amountPerIntake')"
                            >
                                <template #icon>
                                    <Package
                                        :stroke-width="2"
                                        aria-hidden="true"
                                    />
                                </template>
                                {{ doseLine }}
                            </PatientListCardDetailsGroupItem>

                            <PatientListCardDetailsGroupItem
                                v-if="strengthLine !== null"
                                :label="t('patient.medications.fields.strength')"
                            >
                                <template #icon>
                                    <Scale
                                        :stroke-width="2"
                                        aria-hidden="true"
                                    />
                                </template>
                                {{ strengthLine }}
                            </PatientListCardDetailsGroupItem>

                            <PatientListCardDetailsGroupItem
                                v-if="sortedDoseTimes.length > 0"
                                :label="t('patient.medications.fields.doseTime')"
                            >
                                <template #icon>
                                    <Clock
                                        :stroke-width="2"
                                        aria-hidden="true"
                                    />
                                </template>
                                {{ doseTimesDisplay }}
                            </PatientListCardDetailsGroupItem>

                            <PatientListCardDetailsGroupItem
                                v-if="scheduleDateRow !== null"
                                :label="t('patient.medications.fields.intakePeriod')"
                                raw-value
                            >
                                <template #icon>
                                    <Calendar
                                        :stroke-width="2"
                                        aria-hidden="true"
                                    />
                                </template>
                                <p
                                    :class="patientPageCardDetailValueClass"
                                    :aria-label="scheduleDateRow.ariaLabel"
                                >
                                    {{ scheduleDateRow.rangeDisplay }}
                                </p>
                            </PatientListCardDetailsGroupItem>

                            <PatientListCardDetailsGroupItem
                                v-if="prescriptionExpiryDateRow !== null"
                                :label="t('patient.medications.fields.prescriptionExpiryDateShort')"
                                raw-value
                            >
                                <template #icon>
                                    <CalendarClock
                                        :stroke-width="2"
                                        aria-hidden="true"
                                    />
                                </template>
                                <p :class="patientPageCardDetailValueClass">
                                    <time :datetime="prescriptionExpiryDateRow.iso">
                                        {{ prescriptionExpiryDateRow.display }}
                                    </time>
                                </p>
                            </PatientListCardDetailsGroupItem>
                        </PatientListCardDetailsGroup>

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
                    </div>

                    <PatientListCardDetailsToggle
                        mode="collapse"
                        :label="t('patient.medications.cardCollapseHint')"
                        :ariaLabel="t('patient.medications.hideDetails')"
                    />
                </CollapsibleContent>
            </Collapsible>
        </CardContent>
    </Card>
</template>
