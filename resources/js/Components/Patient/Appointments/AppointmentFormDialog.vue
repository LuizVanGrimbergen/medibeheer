<script setup lang="ts">
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

defineProps<{
    open: boolean;
    title: string;
    description: string;
    formId: string;
    idPrefix: string;
    doctorTypeValues: AppointmentDoctorType[];
    showDoctorTypePlaceholder: boolean;
    form: AppointmentFormWithErrors;
    dialogContentClass: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    submit: [];
    cancel: [];
}>();

const { t } = useI18n();
</script>

<template>
    <Dialog
        :open="open"
        @update:open="emit('update:open', $event)"
    >
        <DialogContent :class="dialogContentClass">
            <DialogHeader class="shrink-0 space-y-3 text-left sm:space-y-2">
                <DialogTitle class="pr-14 text-2xl font-bold leading-tight text-text-heading">
                    {{ title }}
                </DialogTitle>
                <DialogDescription class="text-base leading-relaxed text-text-muted">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <form
                :id="formId"
                class="flex min-h-0 flex-1 flex-col gap-6 overflow-hidden"
                novalidate
                @submit.prevent="emit('submit')"
            >
                <div
                    class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch]"
                >
                    <AppointmentFormFields
                        :form="form"
                        :id-prefix="idPrefix"
                        :doctor-type-values="doctorTypeValues"
                        :show-doctor-type-placeholder="showDoctorTypePlaceholder"
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
                        :disabled="form.processing"
                    >
                        {{ t('patient.appointments.actions.save') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
