<script setup lang="ts">
import MedicationFormDialogFooter from '@/Components/Patient/Medications/form/MedicationFormDialogFooter.vue';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { useMedicationFormWizard } from '@/Components/Patient/Medications/form/useMedicationFormWizard';
import MedicationCreateSummaryStep from '@/Components/Patient/Medications/steps/MedicationCreateSummaryStep.vue';
import MedicationDetailsStep from '@/Components/Patient/Medications/steps/MedicationDetailsStep.vue';
import MedicationNoteStep from '@/Components/Patient/Medications/steps/MedicationNoteStep.vue';
import MedicationScheduleDoseTimesStep from '@/Components/Patient/Medications/steps/MedicationScheduleDoseTimesStep.vue';
import MedicationScheduleDurationStep from '@/Components/Patient/Medications/steps/MedicationScheduleDurationStep.vue';
import MedicationScheduleMealsAndFrequencyStep from '@/Components/Patient/Medications/steps/MedicationScheduleMealsAndFrequencyStep.vue';
import MedicationScheduleTimesPerDayStep from '@/Components/Patient/Medications/steps/MedicationScheduleTimesPerDayStep.vue';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { patientShellDialogOverlayAboveAppChromeClass } from '@/lib/patient/patientShellDialogLayout';

const props = defineProps<{
    open: boolean;
    title: string;
    formId: string;
    idPrefix: string;
    form: MedicationCreateFormWithErrors;
    dialogContentClass: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    submit: [];
    cancel: [];
}>();

const {
    currentStep,
    medicationProgressLabel,
    handleSubmit,
    handleMedicationFormFooterBack,
    goToMedicationWizardStepFromSummary,
} = useMedicationFormWizard({
        open: () => props.open,
        form: () => props.form,
        idPrefix: () => props.idPrefix,
        onSubmit: () => {
            emit('submit');
        },
    });
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
                    {{ medicationProgressLabel }}
                </DialogDescription>
            </DialogHeader>

            <form
                :id="props.formId"
                class="flex min-h-0 flex-1 flex-col"
                novalidate
                @submit.prevent="handleSubmit"
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
                                    <MedicationDetailsStep
                                        v-if="currentStep === 1"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationScheduleMealsAndFrequencyStep
                                        v-else-if="currentStep === 2"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationScheduleTimesPerDayStep
                                        v-else-if="currentStep === 3"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationScheduleDoseTimesStep
                                        v-else-if="currentStep === 4"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationScheduleDurationStep
                                        v-else-if="currentStep === 5"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                    <MedicationNoteStep
                                        v-else-if="currentStep === 6"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                        show-stock-fields
                                    />
                                    <MedicationCreateSummaryStep
                                        v-else-if="currentStep === 7"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                        :go-to-wizard-step="goToMedicationWizardStepFromSummary"
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
                            <MedicationFormDialogFooter
                                :current-step="currentStep"
                                :processing="props.form.processing"
                                @cancel="emit('cancel')"
                                @back="handleMedicationFormFooterBack"
                            />
                        </CardContent>
                    </Card>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
