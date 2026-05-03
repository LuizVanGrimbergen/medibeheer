<script setup lang="ts">
import { CheckCircle2, CircleX } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { appointmentOptionalNoteDialogContentClass } from '@/Components/Patient/Appointments/appointmentDialogContentClass';
import OptionalNoteDialog from '@/Components/Patient/Appointments/OptionalNoteDialog.vue';
import { Button } from '@/Components/ui/button';
import type {
    AppointmentCancelledCommitPayload,
    AppointmentDoneCommitPayload,
} from '@/lib/types';
import { cn } from '@/lib/utils';

const props = defineProps<{
    instanceKey: string | number;
    modelValue: boolean;
    disabled: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [done: boolean];
    'commit-done': [payload: AppointmentDoneCommitPayload];
    'commit-cancelled': [payload: AppointmentCancelledCommitPayload];
}>();

const { t } = useI18n();

const completeDialogOpen = ref(false);
const cancelDialogOpen = ref(false);

const doneTextareaId = computed(
    () => `appointment-done-visit-summary-${props.instanceKey}`,
);

const cancelTextareaId = computed(
    () => `appointment-cancel-reason-${props.instanceKey}`,
);

watch(
    () => props.modelValue,
    (done) => {
        if (done) {
            completeDialogOpen.value = false;
            cancelDialogOpen.value = false;
        }
    },
);

function openCompleteDialog(): void {
    if (props.disabled) {
        return;
    }

    cancelDialogOpen.value = false;
    completeDialogOpen.value = true;
}

function openCancelDialog(): void {
    if (props.disabled) {
        return;
    }

    completeDialogOpen.value = false;
    cancelDialogOpen.value = true;
}

function onDoneSubmit(payload: { text: string | null }): void {
    emit('commit-done', {
        doctor_visit_summary: payload.text,
    });
}

function onCancelSubmit(payload: { text: string | null }): void {
    emit('commit-cancelled', {
        cancellation_reason: payload.text,
    });
}

function revert(): void {
    if (props.disabled) {
        return;
    }

    emit('update:modelValue', false);
}
</script>

<template>
    <fieldset class="m-0 min-w-0 border-0 border-t border-border/70 p-0 pt-5">
        <legend class="mb-3 w-full px-0 text-base font-semibold leading-snug text-text-heading">
            {{ t('patient.appointments.doneToggle.groupLabel') }}
        </legend>

        <div v-if="!modelValue">
            <div class="flex flex-col gap-3 sm:flex-row sm:gap-3">
                <Button
                    type="button"
                    variant="default"
                    size="lg"
                    :disabled="disabled"
                    :class="
                        cn(
                            'min-h-14 min-w-0 flex-1 touch-manipulation gap-2.5 px-4 text-lg font-semibold',
                            completeDialogOpen &&
                                'border-2 border-success bg-success text-white shadow-sm hover:bg-success hover:text-white',
                        )
                    "
                    @click="openCompleteDialog"
                >
                    <CheckCircle2
                        class="size-6 shrink-0"
                        aria-hidden="true"
                    />
                    {{ t('patient.appointments.doneToggle.markDone') }}
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="lg"
                    :disabled="disabled"
                    :class="
                        cn(
                            'min-h-14 min-w-0 flex-1 touch-manipulation gap-2.5 border-2 border-danger/50 px-4 text-lg font-semibold text-danger hover:border-danger hover:bg-danger/10 hover:text-danger',
                            cancelDialogOpen && 'border-danger bg-danger/10',
                        )
                    "
                    @click="openCancelDialog"
                >
                    <CircleX
                        class="size-6 shrink-0"
                        aria-hidden="true"
                    />
                    {{ t('patient.appointments.doneToggle.markCancelled') }}
                </Button>
            </div>
        </div>

        <div
            v-else
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4"
        >
            <p
                class="flex items-center gap-2 text-lg font-semibold leading-snug text-success"
            >
                <CheckCircle2
                    class="size-6 shrink-0"
                    aria-hidden="true"
                />
                {{ t('patient.appointments.doneToggle.markDone') }}
            </p>
            <Button
                type="button"
                variant="outline"
                size="lg"
                class="min-h-12 w-full shrink-0 touch-manipulation px-4 text-base font-semibold sm:w-auto"
                :disabled="disabled"
                @click="revert"
            >
                {{ t('patient.appointments.doneToggle.markUndone') }}
            </Button>
        </div>
    </fieldset>

    <OptionalNoteDialog
        :open="completeDialogOpen"
        :title="t('patient.appointments.doneDialog.title')"
        :description="t('patient.appointments.doneDialog.description')"
        :field-label="t('patient.appointments.doneDialog.visitSummaryLabel')"
        :placeholder="t('patient.appointments.doneDialog.visitSummaryPlaceholder')"
        :textarea-id="doneTextareaId"
        :dialog-content-class="appointmentOptionalNoteDialogContentClass"
        :cancel-label="t('patient.appointments.actions.cancel')"
        :confirm-label="t('patient.appointments.doneDialog.confirm')"
        tone="success"
        :disabled="disabled"
        @update:open="(v) => (completeDialogOpen = v)"
        @submit="onDoneSubmit"
    />

    <OptionalNoteDialog
        :open="cancelDialogOpen"
        :title="t('patient.appointments.cancelDialog.title')"
        :description="t('patient.appointments.cancelDialog.description')"
        :field-label="t('patient.appointments.cancelDialog.reasonLabel')"
        :placeholder="t('patient.appointments.cancelDialog.reasonPlaceholder')"
        :textarea-id="cancelTextareaId"
        :dialog-content-class="appointmentOptionalNoteDialogContentClass"
        :cancel-label="t('patient.appointments.actions.cancel')"
        :confirm-label="t('patient.appointments.cancelDialog.confirm')"
        tone="danger"
        :disabled="disabled"
        @update:open="(v) => (cancelDialogOpen = v)"
        @submit="onCancelSubmit"
    />
</template>
