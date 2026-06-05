<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { AlertTriangle, Check } from 'lucide-vue-next';
import type { ComponentPublicInstance } from 'vue';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationListCardLead from '@/Components/Medications/MedicationListCardLead.vue';
import PatientListCardDetailsToggle from '@/Components/Patient/PatientListCardDetailsToggle.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Collapsible, CollapsibleContent } from '@/Components/ui/collapsible';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { useGsapAttentionPulse } from '@/composables/motion/useGsapAttentionPulse';
import { useSuccessFlashTrigger } from '@/composables/motion/useSuccessFlashTrigger';
import { medicationVisualToneFromContext } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationListVisualToneClasses } from '@/lib/patient/inventory/medicationListVisualToneClasses';
import {
    medicationIntakeDoseLine,
    medicationIntakeNotePreview,
    medicationTodayIntakeHeaderSummary,
} from '@/lib/patient/medications/display/medicationIntakeSlotDisplay';
import {
    buildMedicationTakenAtForToday,
    currentMedicationIntakeTimeHHmm,
} from '@/lib/patient/medications/intake/medicationIntakeWindow';
import {
    patientFormFieldInvalidClass,
    patientFormLabelClass,
    patientFormNativeDateTimeInputClass,
} from '@/lib/patient/patientFormFieldClasses';
import { patientPageCardHeaderSummaryClass } from '@/lib/patient/patientPageTypography';
import type {
    MedicationTypeValue,
    TodayMedicationIntakeSlot,
} from '@/lib/types';
import { cn } from '@/lib/utils';

const props = defineProps<{
    intakeSlot: TodayMedicationIntakeSlot;
}>();

const { t } = useI18n();

const isTaken = computed(() => props.intakeSlot.taken_at !== null);

const intakeWindowState = computed(() => props.intakeSlot.intake_window_state);

const showPastSnoozeActions = computed(
    () => !isTaken.value && intakeWindowState.value === 'past',
);

const showStandardTakeButton = computed(
    () => !isTaken.value && intakeWindowState.value === 'within',
);

const showBeforeWindowState = computed(
    () => !isTaken.value && intakeWindowState.value === 'before',
);

const showCustomTimePanel = ref(false);
const customTakenTime = ref(currentMedicationIntakeTimeHHmm());
const isOpen = ref(false);
const criticalAlertRef = ref<HTMLElement | ComponentPublicInstance | null>(
    null,
);
const shouldFlashSuccess = useSuccessFlashTrigger(isTaken);

watch(showPastSnoozeActions, (visible) => {
    if (!visible) {
        showCustomTimePanel.value = false;
    }
});

const form = useForm<{
    medication_schedule_id: number;
    dose_time: string;
    late_intake?: boolean;
    taken_at?: string;
}>({
    medication_schedule_id: props.intakeSlot.medication_schedule_id,
    dose_time: props.intakeSlot.dose_time,
});

const doseLine = computed(() => medicationIntakeDoseLine(t, props.intakeSlot));

const notePreview = computed(() =>
    medicationIntakeNotePreview(props.intakeSlot),
);

const headerSummary = computed(() =>
    medicationTodayIntakeHeaderSummary(t, props.intakeSlot),
);

const stockProgressTone = computed(() =>
    medicationVisualToneFromContext({
        supply_estimate_days: props.intakeSlot.supply_estimate_days,
        supply_estimate_quality: props.intakeSlot.supply_estimate_quality,
    }),
);

const isCriticalSupply = computed(() => stockProgressTone.value === 'critical');

const intakeCardToneClasses = computed(() =>
    medicationListVisualToneClasses(stockProgressTone.value),
);

const showCriticalSupplyAlert = computed(() => isCriticalSupply.value);

useGsapAttentionPulse(criticalAlertRef, showCriticalSupplyAlert);

const markTakenAriaLabel = computed(() =>
    t('patient.dashboard.todayMedications.markTakenAria', {
        name: props.intakeSlot.name,
        time: props.intakeSlot.dose_time,
    }),
);

const markTakenNowAriaLabel = computed(() =>
    t('patient.dashboard.todayMedications.markTakenNowAria', {
        name: props.intakeSlot.name,
        time: props.intakeSlot.dose_time,
    }),
);

const markTakenCustomAriaLabel = computed(() =>
    t('patient.dashboard.todayMedications.markTakenCustomAria', {
        name: props.intakeSlot.name,
        time: props.intakeSlot.dose_time,
    }),
);

const intakeFormError = computed(
    () => form.errors.taken_at ?? form.errors.dose_time,
);

function buildIntakeRequestPayload(
    data: { medication_schedule_id: number; dose_time: string },
    payload: { lateIntake?: boolean; takenAtIso?: string },
): {
    medication_schedule_id: number;
    dose_time: string;
    late_intake?: boolean;
    taken_at?: string;
} {
    const request: {
        medication_schedule_id: number;
        dose_time: string;
        late_intake?: boolean;
        taken_at?: string;
    } = {
        medication_schedule_id: data.medication_schedule_id,
        dose_time: data.dose_time,
    };

    if (payload.lateIntake === true) {
        request.late_intake = true;
    }

    if (payload.takenAtIso) {
        request.taken_at = payload.takenAtIso;
    }

    return request;
}

function submitIntake(payload: {
    lateIntake?: boolean;
    takenAtIso?: string;
}): void {
    if (isTaken.value || form.processing) {
        return;
    }

    form.transform((data) => buildIntakeRequestPayload(data, payload)).post(
        route('patient.medication-intakes.store'),
        {
            preserveScroll: true,
            onSuccess: () => {
                showCustomTimePanel.value = false;
            },
        },
    );
}

function markTakenWithinWindow(): void {
    submitIntake({});
}

function markTakenNow(): void {
    submitIntake({ lateIntake: true });
}

function openCustomTimePanel(): void {
    customTakenTime.value = currentMedicationIntakeTimeHHmm();
    showCustomTimePanel.value = true;
}

function confirmCustomTakenTime(): void {
    const takenAt = buildMedicationTakenAtForToday(customTakenTime.value);

    if (takenAt === null) {
        return;
    }

    submitIntake({ takenAtIso: takenAt });
}
</script>

<template>
    <Card
        class="bg-surface text-text relative w-full min-w-0 overflow-hidden rounded-3xl border shadow-md shadow-black/[0.04]"
        :class="
            isTaken
                ? 'border-success bg-success/5'
                : intakeCardToneClasses.border
        "
    >
        <CardContent class="relative p-6 sm:p-7">
            <AlertTriangle
                v-if="showCriticalSupplyAlert"
                ref="criticalAlertRef"
                class="text-danger pointer-events-none absolute top-6 right-6 z-10 size-6 shrink-0 sm:size-7"
                role="img"
                :aria-label="t('patient.inventory.lowStockBadge')"
            />

            <Collapsible v-model:open="isOpen">
                <MedicationListCardLead
                    :name="intakeSlot.name"
                    :type-medication="
                        intakeSlot.type_medication as MedicationTypeValue
                    "
                    :tone="stockProgressTone"
                    :show-type-label="false"
                    :class="showCriticalSupplyAlert ? 'pr-8 sm:pr-10' : null"
                >
                    <template v-if="!isOpen" #subtitle>
                        <p :class="patientPageCardHeaderSummaryClass">
                            {{ headerSummary }}
                        </p>
                    </template>
                </MedicationListCardLead>

                <div
                    v-if="showBeforeWindowState"
                    class="mt-5 flex flex-col gap-3"
                >
                    <Button
                        type="button"
                        class="min-h-14 w-full touch-manipulation rounded-2xl text-lg font-bold sm:min-h-12 sm:text-base"
                        variant="outline"
                        disabled
                    >
                        {{
                            t(
                                'patient.dashboard.todayMedications.notYetTimeToTake',
                            )
                        }}
                    </Button>
                </div>

                <div
                    v-else-if="showStandardTakeButton"
                    class="mt-5 flex flex-col gap-2"
                >
                    <Button
                        type="button"
                        class="min-h-14 w-full touch-manipulation rounded-2xl text-lg font-bold sm:min-h-12 sm:text-base"
                        :disabled="form.processing"
                        :aria-label="markTakenAriaLabel"
                        @click="markTakenWithinWindow"
                    >
                        {{ t('patient.dashboard.todayMedications.markTaken') }}
                    </Button>
                    <InputError :message="intakeFormError" />
                </div>

                <div
                    v-else-if="showPastSnoozeActions"
                    class="mt-5 flex flex-col gap-3"
                >
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <Button
                            type="button"
                            class="min-h-14 w-full touch-manipulation rounded-2xl text-lg font-bold sm:min-h-12 sm:text-base"
                            :disabled="form.processing"
                            :aria-label="markTakenNowAriaLabel"
                            @click="markTakenNow"
                        >
                            {{
                                t(
                                    'patient.dashboard.todayMedications.markTaken',
                                )
                            }}
                        </Button>
                        <Button
                            type="button"
                            class="min-h-14 w-full touch-manipulation rounded-2xl text-lg font-bold sm:min-h-12 sm:text-base"
                            variant="outline"
                            :disabled="form.processing"
                            :aria-label="markTakenCustomAriaLabel"
                            :aria-expanded="showCustomTimePanel"
                            @click="openCustomTimePanel"
                        >
                            {{
                                t(
                                    'patient.dashboard.todayMedications.markTakenCustom',
                                )
                            }}
                        </Button>
                    </div>

                    <div
                        v-if="showCustomTimePanel"
                        class="border-border/70 bg-bg space-y-3 rounded-2xl border p-4 sm:p-5"
                    >
                        <Label
                            :for="`intake-custom-time-${intakeSlot.medication_schedule_id}-${intakeSlot.dose_time}`"
                            :class="patientFormLabelClass"
                        >
                            {{
                                t(
                                    'patient.dashboard.todayMedications.customTakenTimeLabel',
                                )
                            }}
                        </Label>
                        <input
                            :id="`intake-custom-time-${intakeSlot.medication_schedule_id}-${intakeSlot.dose_time}`"
                            v-model="customTakenTime"
                            type="time"
                            step="60"
                            autocomplete="off"
                            :class="
                                cn(
                                    patientFormNativeDateTimeInputClass,
                                    intakeFormError
                                        ? patientFormFieldInvalidClass
                                        : null,
                                )
                            "
                            :aria-invalid="Boolean(intakeFormError)"
                        />
                        <InputError :message="intakeFormError" />
                        <Button
                            type="button"
                            class="min-h-14 w-full touch-manipulation rounded-2xl text-lg font-bold sm:min-h-12 sm:text-base"
                            :disabled="form.processing"
                            @click="confirmCustomTakenTime"
                        >
                            {{
                                t(
                                    'patient.dashboard.todayMedications.confirmCustomTaken',
                                )
                            }}
                        </Button>
                    </div>
                    <InputError
                        v-if="!showCustomTimePanel"
                        :message="intakeFormError"
                    />
                </div>

                <div v-else-if="isTaken" class="mt-5">
                    <Button
                        type="button"
                        class="min-h-14 w-full touch-manipulation rounded-2xl text-lg font-bold sm:min-h-12 sm:text-base"
                        variant="outline"
                        success
                        :success-flash="shouldFlashSuccess"
                        disabled
                        :aria-pressed="true"
                    >
                        <Check
                            class="text-success size-6 shrink-0 sm:size-5"
                            aria-hidden="true"
                        />
                        {{ t('patient.dashboard.todayMedications.taken') }}
                    </Button>
                </div>

                <CollapsibleContent>
                    <div class="pt-4">
                        <div class="flex flex-col gap-4 sm:gap-5">
                            <div
                                class="grid min-w-0 grid-cols-1 gap-3 sm:gap-4"
                                :class="
                                    doseLine !== null ? 'sm:grid-cols-2' : null
                                "
                            >
                                <div
                                    v-if="doseLine !== null"
                                    class="border-border/70 bg-bg flex min-w-0 flex-col justify-center gap-1.5 rounded-2xl border px-4 py-3.5 sm:gap-2 sm:px-5 sm:py-4"
                                >
                                    <span
                                        class="text-text-muted text-sm leading-snug font-semibold sm:text-base"
                                    >
                                        {{
                                            t(
                                                'patient.dashboard.todayMedications.intakeCard.dose',
                                            )
                                        }}
                                    </span>
                                    <span
                                        class="text-text-heading text-xl leading-tight font-bold wrap-break-word tabular-nums sm:text-2xl"
                                    >
                                        {{ doseLine }}
                                    </span>
                                </div>

                                <div
                                    class="border-border/70 bg-bg flex min-w-0 flex-col justify-center gap-1.5 rounded-2xl border px-4 py-3.5 sm:gap-2 sm:px-5 sm:py-4"
                                >
                                    <span
                                        class="text-text-muted text-sm leading-snug font-semibold sm:text-base"
                                    >
                                        {{
                                            t(
                                                'patient.dashboard.todayMedications.intakeCard.time',
                                            )
                                        }}
                                    </span>
                                    <span
                                        class="text-text-heading text-xl leading-tight font-bold tabular-nums sm:text-2xl"
                                    >
                                        {{ intakeSlot.dose_time }}
                                    </span>
                                </div>
                            </div>

                            <div
                                v-if="notePreview !== null"
                                class="flex flex-col gap-1.5"
                            >
                                <span
                                    class="text-text-muted text-sm font-semibold sm:text-base"
                                >
                                    {{
                                        t(
                                            'patient.dashboard.todayMedications.intakeCard.note',
                                        )
                                    }}
                                </span>
                                <p
                                    class="text-text min-w-0 text-base leading-relaxed wrap-break-word whitespace-pre-wrap sm:text-lg"
                                >
                                    {{ notePreview }}
                                </p>
                            </div>
                        </div>
                    </div>
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
            </Collapsible>
        </CardContent>
    </Card>
</template>
