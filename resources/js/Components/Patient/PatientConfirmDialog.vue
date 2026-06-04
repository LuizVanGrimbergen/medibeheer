<script setup lang="ts">
import { computed, useId } from 'vue';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogContent } from '@/Components/ui/dialog';
import {
    patientConfirmDialogContentClass,
    patientConfirmDialogMessageClass,
    patientFormWizardFooterCancelButtonClass,
    patientFormWizardFooterOutlineButtonClass,
    patientFormWizardFooterPrimaryButtonClass,
    patientFormWizardFooterRowClass,
    patientShellDialogOverlayAboveAppChromeClass,
    patientShellWizardFooterClass,
} from '@/lib/patient/patientShellDialogLayout';
import {
    patientActionSuccessSubtitleClass,
    patientActionSuccessTitleClass,
} from '@/lib/patient/patientPageTypography';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description: string;
        confirmLabel: string;
        cancelLabel: string;
        processing?: boolean;
        /** Danger for revoke/delete; primary for neutral confirmations. */
        confirmTone?: 'danger' | 'primary';
    }>(),
    {
        processing: false,
        confirmTone: 'danger',
    },
);

const emit = defineEmits<{
    'update:open': [open: boolean];
    confirm: [];
}>();

const titleId = useId();
const descriptionId = useId();

const confirmButtonClass = computed(() =>
    props.confirmTone === 'primary'
        ? patientFormWizardFooterPrimaryButtonClass
        : patientFormWizardFooterCancelButtonClass,
);

const confirmButtonVariant = computed(() =>
    props.confirmTone === 'primary' ? 'default' : 'secondary',
);

function close(): void {
    emit('update:open', false);
}

function confirm(): void {
    emit('confirm');
}
</script>

<template>
    <Dialog :open="open" @update:open="(value) => emit('update:open', value)">
        <DialogContent
            :class="
                cn(patientConfirmDialogContentClass, 'flex min-h-0 flex-col')
            "
            :overlay-class="patientShellDialogOverlayAboveAppChromeClass('md')"
            :aria-describedby="descriptionId"
            :aria-labelledby="titleId"
        >
            <div :class="patientConfirmDialogMessageClass">
                <h1 :id="titleId" :class="patientActionSuccessTitleClass">
                    {{ title }}
                </h1>
                <p :id="descriptionId" :class="patientActionSuccessSubtitleClass">
                    {{ description }}
                </p>
            </div>

            <div
                :class="[
                    patientFormWizardFooterRowClass,
                    patientShellWizardFooterClass,
                    'mt-auto shrink-0',
                ]"
            >
                <Button
                    type="button"
                    :variant="confirmButtonVariant"
                    size="lg"
                    :class="confirmButtonClass"
                    :disabled="processing"
                    @click="confirm"
                >
                    {{ confirmLabel }}
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="lg"
                    :class="patientFormWizardFooterOutlineButtonClass"
                    :disabled="processing"
                    @click="close"
                >
                    {{ cancelLabel }}
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
