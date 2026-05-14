<script setup lang="ts">
import { nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type {
    MedicationCreateFormWithErrors,
    MedicationFormWizardStep,
} from '@/Components/Patient/Medications/form/MedicationFormTypes';
import MedicationCreateSummaryStep from '@/Components/Patient/Medications/steps/MedicationCreateSummaryStep.vue';
import MedicationDetailsStep from '@/Components/Patient/Medications/steps/MedicationDetailsStep.vue';
import MedicationNoteStep from '@/Components/Patient/Medications/steps/MedicationNoteStep.vue';
import MedicationScheduleDoseTimesStep from '@/Components/Patient/Medications/steps/MedicationScheduleDoseTimesStep.vue';
import MedicationScheduleDurationStep from '@/Components/Patient/Medications/steps/MedicationScheduleDurationStep.vue';
import MedicationScheduleMealsAndFrequencyStep from '@/Components/Patient/Medications/steps/MedicationScheduleMealsAndFrequencyStep.vue';
import MedicationScheduleTimesPerDayStep from '@/Components/Patient/Medications/steps/MedicationScheduleTimesPerDayStep.vue';
import { buttonVariants } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { patientShellDialogOverlayAboveAppChromeClass } from '@/lib/patient/patientShellDialogLayout';
import { cn } from '@/lib/utils';

type MedicationEditDialogStep = 0 | 1 | 2 | 3 | 4 | 5 | 6;

const props = defineProps<{
    open: boolean;
    title: string;
    formId: string;
    idPrefix: string;
    form: MedicationCreateFormWithErrors;
    dialogContentClass: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    submit: [];
    cancel: [];
}>();

const { t } = useI18n();

const editingStep = ref<MedicationEditDialogStep>(0);

watch(
    () => props.open,
    (open) => {
        if (open) {
            editingStep.value = 0;
        }
    },
);

function medicationEditSummaryGoToField(
    step: MedicationFormWizardStep,
    focusElementIdSuffix?: string,
): void {
    if (props.processing) {
        return;
    }

    if (step < 1 || step > 6) {
        return;
    }

    editingStep.value = step as MedicationEditDialogStep;

    void nextTick(() => {
        const suffix =
            focusElementIdSuffix !== undefined && focusElementIdSuffix.length > 0
                ? focusElementIdSuffix
                : 'name';

        const el = document.getElementById(`${props.idPrefix}-${suffix}`);

        if (el === null) {
            return;
        }

        el.focus({ preventScroll: true });
        el.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    });
}

const primaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation gap-2.5 rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

const secondaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl border-2 border-danger/40 bg-danger/10 px-3 text-base font-semibold text-danger hover:border-danger hover:bg-danger/20 hover:text-danger md:min-h-14 md:flex-1 md:px-4 lg:text-lg';
</script>

<template>
    <Dialog
        :open="props.open"
        @update:open="emit('update:open', $event)"
    >
        <DialogContent
            :class="props.dialogContentClass"
            :overlay-class="patientShellDialogOverlayAboveAppChromeClass('md')"
        >
            <DialogHeader class="shrink-0 space-y-1.5 pt-[env(safe-area-inset-top,0)] text-left sm:space-y-1 sm:pt-0 md:space-y-1">
                <DialogTitle class="text-xl font-bold leading-tight text-text-heading md:text-2xl">
                    {{ props.title }}
                </DialogTitle>
                <DialogDescription
                    class="block text-sm font-medium leading-snug text-text-heading md:text-base md:leading-relaxed"
                    aria-live="polite"
                >
                    {{
                        editingStep >= 1
                            ? t('patient.medications.stepsProgress', {
                                  current: editingStep,
                                  total: 7,
                              })
                            : t('patient.medications.stepsProgress', { current: 7, total: 7 })
                    }}
                </DialogDescription>
            </DialogHeader>

            <form
                :id="props.formId"
                class="flex min-h-0 flex-1 flex-col"
                novalidate
                @submit.prevent="emit('submit')"
            >
                <div
                    class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch] touch-pan-y"
                >
                    <div class="space-y-3 md:space-y-3">
                        <Card
                            class="rounded-2xl border border-border/80 bg-surface text-text shadow-md shadow-black/[0.04] md:rounded-3xl"
                        >
                            <CardContent class="p-0">
                                <div
                                    class="space-y-5 rounded-2xl bg-surface px-4 py-4 md:space-y-5 md:rounded-3xl md:px-5 md:py-5 lg:space-y-6 lg:px-7 lg:py-7"
                                >
                                    <MedicationCreateSummaryStep
                                        v-if="editingStep === 0"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                        :go-to-wizard-step="medicationEditSummaryGoToField"
                                        :always-show-note-summary-row="true"
                                    />
                                    <MedicationDetailsStep
                                        v-else-if="editingStep === 1"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationScheduleMealsAndFrequencyStep
                                        v-else-if="editingStep === 2"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationScheduleTimesPerDayStep
                                        v-else-if="editingStep === 3"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationScheduleDoseTimesStep
                                        v-else-if="editingStep === 4"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationScheduleDurationStep
                                        v-else-if="editingStep === 5"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationNoteStep
                                        v-else-if="editingStep === 6"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                        show-stock-fields
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <div
                    class="pointer-events-auto relative z-10 shrink-0 pt-2 pb-[max(0.75rem,env(safe-area-inset-bottom,0px))]"
                >
                    <Card
                        class="rounded-2xl border border-border/80 bg-transparent text-text shadow-sm shadow-black/[0.03] md:rounded-3xl"
                    >
                        <CardContent class="px-4 py-3 md:px-5 md:py-3.5 lg:px-7 lg:py-4">
                            <div
                                class="flex min-w-0 flex-col gap-2 md:flex-row-reverse md:flex-wrap md:gap-3"
                            >
                                <button
                                    v-if="editingStep >= 1"
                                    type="submit"
                                    :disabled="props.processing"
                                    :class="
                                        cn(
                                            buttonVariants({
                                                variant: 'default',
                                                size: 'lg',
                                            }),
                                            primaryButtonClass,
                                        )
                                    "
                                >
                                    {{ t('patient.medications.actions.save') }}
                                </button>
                                <button
                                    type="button"
                                    :disabled="props.processing"
                                    :class="
                                        cn(
                                            buttonVariants({
                                                variant: 'secondary',
                                                size: 'lg',
                                            }),
                                            secondaryButtonClass,
                                        )
                                    "
                                    @click.stop.prevent="emit('cancel')"
                                >
                                    {{ t('patient.medications.actions.cancel') }}
                                </button>
                                <button
                                    v-if="editingStep >= 1"
                                    type="button"
                                    :disabled="props.processing"
                                    :class="
                                        cn(
                                            buttonVariants({
                                                variant: 'outline',
                                                size: 'lg',
                                            }),
                                            'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg',
                                        )
                                    "
                                    @click.stop.prevent="editingStep = 0"
                                >
                                    {{ t('patient.medications.actions.backToOverview') }}
                                </button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
