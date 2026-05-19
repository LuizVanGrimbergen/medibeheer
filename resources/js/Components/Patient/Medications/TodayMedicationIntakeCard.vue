<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { AlertTriangle, Check } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationTypeLeadIcon from '@/Components/Medications/MedicationTypeLeadIcon.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { medicationVisualToneFromContext } from '@/lib/patient/inventory/medicationListVisualTone';
import { medicationListVisualToneClasses } from '@/lib/patient/inventory/medicationListVisualToneClasses';
import {
    medicationIntakeDoseLine,
    medicationIntakeNotePreview,
    medicationTypeLabel,
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
import type { TodayMedicationIntakeSlot } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = defineProps<{
    slot: TodayMedicationIntakeSlot;
}>();

const { t } = useI18n();

const isTaken = computed(() => props.slot.taken_at !== null);

const intakeWindowState = computed(() => props.slot.intake_window_state);

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
    medication_schedule_id: props.slot.medication_schedule_id,
    dose_time: props.slot.dose_time,
});

const doseLine = computed(() => medicationIntakeDoseLine(t, props.slot));

const notePreview = computed(() => medicationIntakeNotePreview(props.slot));

const typeLabel = computed(() => medicationTypeLabel(t, props.slot.type_medication));

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

const intakeFormError = computed(() => form.errors.taken_at ?? form.errors.dose_time);

function buildIntakeRequestPayload(
    data: { medication_schedule_id: number; dose_time: string },
    payload: { lateIntake?: boolean; takenAtIso?: string },
): { medication_schedule_id: number; dose_time: string; late_intake?: boolean; taken_at?: string } {
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

function submitIntake(payload: { lateIntake?: boolean; takenAtIso?: string }): void {
    if (isTaken.value || form.processing) {
        return;
    }

    form
        .transform((data) => buildIntakeRequestPayload(data, payload))
        .post(route('patient.medication-intakes.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showCustomTimePanel.value = false;
            },
        });
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
        class="min-w-0 w-full rounded-3xl border-2 bg-surface text-text shadow-md shadow-black/[0.04]"
        :class="intakeCardToneClasses.border"
    >
        <CardContent class="relative flex flex-col gap-5 p-5 sm:gap-6 sm:p-6">
            <AlertTriangle
                v-if="showCriticalSupplyAlert"
                class="pointer-events-none absolute right-4 top-4 z-10 size-6 shrink-0 animate-supply-alert-flicker text-danger sm:right-6 sm:top-6 sm:size-7"
                role="img"
                :aria-label="t('patient.inventory.lowStockBadge')"
            />

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

            <div
                v-if="showBeforeWindowState"
                class="flex flex-col gap-3"
            >
                <Button
                    type="button"
                    class="min-h-14 w-full touch-manipulation rounded-2xl text-lg font-bold sm:min-h-12 sm:text-base"
                    variant="outline"
                    disabled
                >
                    {{ t('patient.dashboard.todayMedications.notYetTimeToTake') }}
                </Button>
            </div>

            <div
                v-else-if="showStandardTakeButton"
                class="flex flex-col gap-2"
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
                class="flex flex-col gap-3"
            >
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <Button
                        type="button"
                        class="min-h-14 w-full touch-manipulation rounded-2xl text-lg font-bold sm:min-h-12 sm:text-base"
                        :disabled="form.processing"
                        @click="markTakenNow"
                    >
                        {{ t('patient.dashboard.todayMedications.markTakenNow') }}
                    </Button>
                    <Button
                        type="button"
                        class="min-h-14 w-full touch-manipulation rounded-2xl text-lg font-bold sm:min-h-12 sm:text-base"
                        variant="outline"
                        :disabled="form.processing"
                        :aria-expanded="showCustomTimePanel"
                        @click="openCustomTimePanel"
                    >
                        {{ t('patient.dashboard.todayMedications.markTakenCustom') }}
                    </Button>
                </div>

                <div
                    v-if="showCustomTimePanel"
                    class="space-y-3 rounded-2xl border border-border/70 bg-bg p-4 sm:p-5"
                >
                    <Label
                        :for="`intake-custom-time-${slot.medication_schedule_id}-${slot.dose_time}`"
                        :class="patientFormLabelClass"
                    >
                        {{ t('patient.dashboard.todayMedications.customTakenTimeLabel') }}
                    </Label>
                    <input
                        :id="`intake-custom-time-${slot.medication_schedule_id}-${slot.dose_time}`"
                        v-model="customTakenTime"
                        type="time"
                        step="60"
                        autocomplete="off"
                        :class="
                            cn(
                                patientFormNativeDateTimeInputClass,
                                intakeFormError ? patientFormFieldInvalidClass : null,
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
                        {{ t('patient.dashboard.todayMedications.confirmCustomTaken') }}
                    </Button>
                </div>
                <InputError
                    v-if="!showCustomTimePanel"
                    :message="intakeFormError"
                />
            </div>

            <Button
                v-else-if="isTaken"
                type="button"
                class="min-h-14 w-full touch-manipulation rounded-2xl border-2 border-success bg-success/10 text-lg font-bold text-text-heading hover:bg-success/10 sm:min-h-12 sm:text-base"
                variant="outline"
                disabled
                :aria-pressed="true"
            >
                <Check
                    class="size-6 shrink-0 text-success sm:size-5"
                    aria-hidden="true"
                />
                {{ t('patient.dashboard.todayMedications.taken') }}
            </Button>
        </CardContent>
    </Card>
</template>
