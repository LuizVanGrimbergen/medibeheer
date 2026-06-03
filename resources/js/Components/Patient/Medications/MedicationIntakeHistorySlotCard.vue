<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationTypeLeadIcon from '@/Components/Medications/MedicationTypeLeadIcon.vue';
import { Card, CardContent } from '@/Components/ui/card';
import {
    medicationIntakeDoseLine,
    medicationIntakeNotePreview,
    medicationTypeLabel,
} from '@/lib/patient/medications/display/medicationIntakeSlotDisplay';
import type { MedicationIntakeHistorySlot } from '@/lib/patient/medications/history/medicationIntakeHistoryTypes';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        intakeSlot: MedicationIntakeHistorySlot;
        density?: 'default' | 'compact';
    }>(),
    {
        density: 'default',
    },
);

const { t } = useI18n();

const isTaken = computed(() => props.intakeSlot.taken_at !== null);

const doseLine = computed(() => medicationIntakeDoseLine(t, props.intakeSlot));

const notePreview = computed(() =>
    medicationIntakeNotePreview(props.intakeSlot),
);

const typeLabel = computed(() =>
    medicationTypeLabel(t, props.intakeSlot.type_medication),
);
</script>

<template>
    <Card
        class="border-border bg-surface text-text w-full min-w-0 rounded-2xl border shadow-sm"
    >
        <CardContent
            :class="
                cn(
                    props.density === 'compact'
                        ? 'p-4'
                        : 'flex flex-col gap-4 p-5 sm:gap-5 sm:p-6',
                )
            "
        >
            <div class="flex min-w-0 items-start gap-4">
                <div
                    class="bg-primary/10 flex size-12 shrink-0 items-center justify-center rounded-2xl sm:size-14"
                >
                    <MedicationTypeLeadIcon
                        :medication-type="intakeSlot.type_medication"
                        icon-tone-class="text-primary"
                    />
                </div>
                <div class="min-w-0 flex-1">
                    <h4
                        class="text-text-heading text-lg leading-snug font-bold wrap-break-word"
                    >
                        {{ intakeSlot.name }}
                    </h4>
                    <p class="text-text-muted mt-1 text-sm font-medium">
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

            <template v-if="props.density === 'default'">
                <div
                    class="grid min-w-0 gap-3"
                    :class="doseLine !== null ? 'grid-cols-2' : 'grid-cols-1'"
                >
                    <div
                        v-if="doseLine !== null"
                        class="border-border/70 bg-bg flex min-w-0 flex-col gap-1 rounded-xl border px-3 py-2.5"
                    >
                        <span class="text-text-muted text-xs font-semibold">
                            {{
                                t(
                                    'patient.dashboard.todayMedications.intakeCard.dose',
                                )
                            }}
                        </span>
                        <span
                            class="text-text-heading text-base font-bold tabular-nums"
                        >
                            {{ doseLine }}
                        </span>
                    </div>
                    <div
                        class="border-border/70 bg-bg flex min-w-0 flex-col gap-1 rounded-xl border px-3 py-2.5"
                    >
                        <span class="text-text-muted text-xs font-semibold">
                            {{
                                t(
                                    'patient.dashboard.todayMedications.intakeCard.time',
                                )
                            }}
                        </span>
                        <span
                            class="text-text-heading text-base font-bold tabular-nums"
                        >
                            {{ intakeSlot.dose_time }}
                        </span>
                    </div>
                </div>

                <p
                    v-if="notePreview !== null"
                    class="text-text min-w-0 text-sm leading-relaxed wrap-break-word whitespace-pre-wrap"
                >
                    <span class="text-text-muted font-semibold">
                        {{
                            t(
                                'patient.dashboard.todayMedications.intakeCard.note',
                            )
                        }}:
                    </span>
                    {{ notePreview }}
                </p>
            </template>
        </CardContent>
    </Card>
</template>
