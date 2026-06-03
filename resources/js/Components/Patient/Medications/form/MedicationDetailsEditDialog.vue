<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { useMedicationEditDialog } from '@/Components/Patient/Medications/form/useMedicationEditDialog';
import MedicationCreateSummaryStep from '@/Components/Patient/Medications/steps/MedicationCreateSummaryStep.vue';
import MedicationDetailsStep from '@/Components/Patient/Medications/steps/MedicationDetailsStep.vue';
import MedicationNoteStep from '@/Components/Patient/Medications/steps/MedicationNoteStep.vue';
import MedicationScheduleDoseTimesStep from '@/Components/Patient/Medications/steps/MedicationScheduleDoseTimesStep.vue';
import MedicationScheduleDurationStep from '@/Components/Patient/Medications/steps/MedicationScheduleDurationStep.vue';
import MedicationScheduleMealsAndFrequencyStep from '@/Components/Patient/Medications/steps/MedicationScheduleMealsAndFrequencyStep.vue';
import MedicationScheduleTimesPerDayStep from '@/Components/Patient/Medications/steps/MedicationScheduleTimesPerDayStep.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { usePatientFormWizardStepMotion } from '@/composables/motion/usePatientFormWizardStepMotion';
import { patientShellDialogOverlayAboveAppChromeClass } from '@/lib/patient/patientShellDialogLayout';
import { ref, toRef } from 'vue';

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

const { editingStep, handleSubmit, medicationEditSummaryGoToField } =
    useMedicationEditDialog({
        open: () => props.open,
        form: () => props.form,
        idPrefix: () => props.idPrefix,
        onSubmit: () => {
            emit('submit');
        },
    });

const primaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation gap-2.5 rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

const secondaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl border-2 border-danger/40 bg-danger/10 px-3 text-base font-semibold text-danger hover:border-danger hover:bg-danger/20 hover:text-danger md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

const isOpen = toRef(() => props.open);
const progressLabelRef = ref<HTMLElement | null>(null);

const { wizardStepPanelRef } = usePatientFormWizardStepMotion(
    editingStep,
    isOpen,
    { progressLabelRef },
);
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent
            :class="props.dialogContentClass"
            :overlay-class="patientShellDialogOverlayAboveAppChromeClass('md')"
        >
            <DialogHeader
                class="shrink-0 space-y-1.5 pt-[env(safe-area-inset-top,0)] text-left sm:space-y-1 sm:pt-0 md:space-y-1"
            >
                <DialogTitle
                    class="text-text-heading text-xl leading-tight font-bold md:text-2xl"
                >
                    {{ props.title }}
                </DialogTitle>
                <DialogDescription
                    ref="progressLabelRef"
                    class="text-text-heading block text-sm leading-snug font-medium md:text-base md:leading-relaxed"
                    aria-live="polite"
                >
                    {{
                        editingStep >= 1
                            ? t('patient.medications.stepsProgress', {
                                  current: editingStep,
                                  total: 7,
                              })
                            : t('patient.medications.stepsProgress', {
                                  current: 7,
                                  total: 7,
                              })
                    }}
                </DialogDescription>
            </DialogHeader>

            <form
                :id="props.formId"
                class="flex min-h-0 flex-1 flex-col"
                novalidate
                @submit.prevent="handleSubmit"
            >
                <div
                    class="min-h-0 flex-1 touch-pan-y overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch]"
                >
                    <div ref="wizardStepPanelRef" class="space-y-3 md:space-y-3">
                        <MedicationScheduleMealsAndFrequencyStep
                            v-if="editingStep === 2"
                            :form="props.form"
                            :id-prefix="props.idPrefix"
                        />
                        <Card
                            v-else
                            class="border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] md:rounded-3xl"
                        >
                            <CardContent class="p-0">
                                <div
                                    class="bg-surface space-y-5 rounded-2xl px-4 py-4 md:space-y-5 md:rounded-3xl md:px-5 md:py-5 lg:space-y-6 lg:px-7 lg:py-7"
                                >
                                    <MedicationCreateSummaryStep
                                        v-if="editingStep === 0"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                        :go-to-wizard-step="
                                            medicationEditSummaryGoToField
                                        "
                                        :always-show-note-summary-row="true"
                                    />
                                    <MedicationDetailsStep
                                        v-else-if="editingStep === 1"
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
                        class="border-border/80 text-text rounded-2xl border bg-transparent shadow-sm shadow-black/[0.03] md:rounded-3xl"
                    >
                        <CardContent
                            class="px-4 py-3 md:px-5 md:py-3.5 lg:px-7 lg:py-4"
                        >
                            <div
                                class="flex min-w-0 flex-col gap-2 md:flex-row-reverse md:flex-wrap md:gap-3"
                            >
                                <Button
                                    v-if="editingStep >= 1"
                                    type="submit"
                                    variant="default"
                                    size="lg"
                                    :disabled="props.processing"
                                    :class="primaryButtonClass"
                                >
                                    {{ t('patient.medications.actions.save') }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    size="lg"
                                    :disabled="props.processing"
                                    :class="secondaryButtonClass"
                                    @click.stop.prevent="emit('cancel')"
                                >
                                    {{
                                        t('patient.medications.actions.cancel')
                                    }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
