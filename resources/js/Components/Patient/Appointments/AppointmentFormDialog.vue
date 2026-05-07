<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AppointmentFormFields from '@/Components/Patient/Appointments/AppointmentFormFields.vue';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/appointmentFormTypes';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import type { AppointmentDoctorType } from '@/lib/types';

const props = defineProps<{
    open: boolean;
    title: string;
    description: string;
    formId: string;
    idPrefix: string;
    doctorTypeValues: AppointmentDoctorType[];
    showDoctorTypePlaceholder: boolean;
    form: AppointmentFormWithErrors;
    transportFamilies: {
        id: number;
        name: string;
    }[];
    dialogContentClass: string;
    submitDisabled?: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    submit: [];
    cancel: [];
}>();

const { t } = useI18n();

const scrollArea = ref<HTMLElement | null>(null);
</script>

<template>
    <Dialog
        :open="props.open"
        @update:open="emit('update:open', $event)"
    >
        <DialogContent :class="props.dialogContentClass">
            <DialogHeader class="shrink-0 space-y-3 text-left sm:space-y-2">
                <DialogTitle class="pr-14 text-2xl font-bold leading-tight text-text-heading">
                    {{ props.title }}
                </DialogTitle>
                <DialogDescription class="text-base leading-relaxed text-text-muted">
                    {{ props.description }}
                </DialogDescription>
            </DialogHeader>

            <form
                :id="props.formId"
                class="flex min-h-0 flex-1 flex-col gap-6"
                novalidate
                @submit.prevent="emit('submit')"
            >
                <div
                    class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain pb-24 [-webkit-overflow-scrolling:touch]"
                    ref="scrollArea"
                >
                    <AppointmentFormFields
                        :form="props.form"
                        :id-prefix="props.idPrefix"
                        :doctor-type-values="props.doctorTypeValues"
                        :show-doctor-type-placeholder="props.showDoctorTypePlaceholder"
                        :transport-families="props.transportFamilies"
                    />
                </div>

                <DialogFooter
                    class="mt-0 flex w-full shrink-0 flex-col-reverse gap-4 border-t border-border/60 pt-4 sm:flex-row sm:justify-end sm:border-t-0 sm:pt-0"
                >
                    <Button
                        type="button"
                        variant="outline"
                        size="lg"
                        class="min-h-14 w-full touch-manipulation border-2 text-lg sm:w-auto sm:min-w-40"
                        @click="emit('cancel')"
                    >
                        {{ t('patient.appointments.actions.cancel') }}
                    </Button>
                    <Button
                        type="submit"
                        size="lg"
                        class="min-h-14 w-full touch-manipulation text-lg sm:w-auto sm:min-w-40"
                        :disabled="props.form.processing || props.submitDisabled === true"
                    >
                        {{ t('patient.appointments.actions.save') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
