<script setup lang="ts">
import { CircleCheck } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import TodayTakenMedicationIntakeRow from '@/Components/Patient/Medications/TodayTakenMedicationIntakeRow.vue';
import PatientListCardDetailsToggle from '@/Components/Patient/PatientListCardDetailsToggle.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Collapsible, CollapsibleContent } from '@/Components/ui/collapsible';
import { medicationUrgencyToneClasses } from '@/lib/patient/medications/urgency/medicationUrgencyToneClasses';
import {
    mobileShellMedicationListCardLeadIconWrapClass,
    mobileShellMedicationListCardLeadRowClass,
    mobileShellMedicationListCardLeadTitleClass,
} from '@/lib/shell/mobileShellTypography';
import type { TodayMedicationIntakeSlot } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = defineProps<{
    takenSlots: TodayMedicationIntakeSlot[];
}>();

const { t } = useI18n();

const isOpen = ref(false);

const takenCount = computed(() => props.takenSlots.length);

const collapsedIconToneClasses = medicationUrgencyToneClasses('safe');

function slotKey(slot: TodayMedicationIntakeSlot): string {
    return `${slot.medication_schedule_id}-${slot.dose_time}`;
}
</script>

<template>
    <section
        v-if="takenCount > 0"
        class="space-y-3 sm:space-y-4"
        :aria-label="t('patient.dashboard.todayMedications.takenSection.title')"
    >
        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 px-0.5">
            <h3 class="text-text-heading text-lg font-bold sm:text-xl">
                {{ t('patient.dashboard.todayMedications.takenSection.title') }}
            </h3>
            <span
                class="text-text-muted text-base font-semibold tabular-nums sm:text-lg"
            >
                ({{ takenCount }})
            </span>
        </div>

        <Collapsible v-model:open="isOpen">
            <Card
                class="border-border/80 bg-surface text-text w-full min-w-0 overflow-hidden rounded-3xl border shadow-md shadow-black/[0.04]"
            >
                <CardContent class="relative p-6 sm:p-7">
                    <div :class="mobileShellMedicationListCardLeadRowClass">
                        <div
                            :class="
                                cn(
                                    mobileShellMedicationListCardLeadIconWrapClass,
                                    collapsedIconToneClasses.pillWrap,
                                )
                            "
                            aria-hidden="true"
                        >
                            <CircleCheck
                                :class="
                                    cn(
                                        'size-6 shrink-0',
                                        collapsedIconToneClasses.pillIcon,
                                    )
                                "
                                aria-hidden="true"
                                stroke-width="2"
                            />
                        </div>

                        <p
                            :class="
                                cn(
                                    mobileShellMedicationListCardLeadTitleClass,
                                    'min-w-0 flex-1',
                                )
                            "
                        >
                            {{
                                t(
                                    'patient.dashboard.todayMedications.takenSection.cardLead',
                                    { count: String(takenCount) },
                                )
                            }}
                        </p>
                    </div>

                    <CollapsibleContent>
                        <ul class="border-border/70 mt-4 border-t pt-1">
                            <li
                                v-for="(slot, index) in takenSlots"
                                :key="slotKey(slot)"
                                class="min-w-0"
                                :class="
                                    index < takenCount - 1
                                        ? 'border-border/70 border-b'
                                        : null
                                "
                            >
                                <TodayTakenMedicationIntakeRow
                                    :intake-slot="slot"
                                />
                            </li>
                        </ul>
                    </CollapsibleContent>

                    <PatientListCardDetailsToggle
                        :mode="isOpen ? 'collapse' : 'expand'"
                        :label="
                            t(
                                isOpen
                                    ? 'patient.medications.cardCollapseHint'
                                    : 'patient.medications.cardExpandHint',
                            )
                        "
                        :ariaLabel="
                            t(
                                isOpen
                                    ? 'patient.medications.hideDetails'
                                    : 'patient.medications.showDetails',
                            )
                        "
                    />
                </CardContent>
            </Card>
        </Collapsible>
    </section>
</template>
