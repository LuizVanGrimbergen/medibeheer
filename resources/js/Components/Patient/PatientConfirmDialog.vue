<script setup lang="ts">
import type { Component } from 'vue';
import { computed, useId } from 'vue';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogContent } from '@/Components/ui/dialog';
import {
    patientActionSuccessSubtitleClass,
    patientActionSuccessTitleClass,
} from '@/lib/patient/patientPageTypography';
import {
    patientConfirmDialogContentClass,
    patientConfirmDialogIconClass,
    patientConfirmDialogIconWrapClass,
    patientConfirmDialogIconWrapDangerClass,
    patientConfirmDialogIconWrapPrimaryClass,
    patientConfirmDialogMessageClass,
    patientFormWizardFooterCancelButtonClass,
    patientFormWizardFooterOutlineButtonClass,
    patientFormWizardFooterPrimaryButtonClass,
    patientFormWizardFooterRowClass,
    patientShellDialogOverlayAboveAppChromeClass,
    patientShellWizardFooterClass,
} from '@/lib/patient/patientShellDialogLayout';
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
        /** Styles the optional Lucide icon wrapper. */
        iconTone?: 'danger' | 'primary';
        /** Lucide icon component (e.g. LogOut from lucide-vue-next). */
        icon?: Component;
        /** Render cancel above confirm and use primary styling for cancel. */
        cancelFirst?: boolean;
        cancelTone?: 'outline' | 'primary';
    }>(),
    {
        processing: false,
        confirmTone: 'danger',
        iconTone: 'danger',
        cancelFirst: false,
        cancelTone: 'outline',
    },
);

const emit = defineEmits<{
    'update:open': [open: boolean];
    confirm: [];
}>();

const titleId = useId();
const descriptionId = useId();

const iconWrapClass = computed(() =>
    props.iconTone === 'primary'
        ? patientConfirmDialogIconWrapPrimaryClass
        : patientConfirmDialogIconWrapDangerClass,
);

const confirmButtonClass = computed(() =>
    props.confirmTone === 'primary'
        ? patientFormWizardFooterPrimaryButtonClass
        : patientFormWizardFooterCancelButtonClass,
);

const confirmButtonVariant = computed(() =>
    props.confirmTone === 'primary' ? 'default' : 'secondary',
);

const footerRowClass = computed(() =>
    props.cancelFirst
        ? 'flex w-full min-w-0 flex-col gap-2 md:flex-row md:gap-3'
        : patientFormWizardFooterRowClass,
);

const cancelButtonClass = computed(() =>
    props.cancelTone === 'primary'
        ? patientFormWizardFooterPrimaryButtonClass
        : patientFormWizardFooterOutlineButtonClass,
);

const cancelButtonVariant = computed(() =>
    props.cancelTone === 'primary' ? 'default' : 'outline',
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
                <div
                    v-if="props.icon !== undefined"
                    :class="
                        cn(patientConfirmDialogIconWrapClass, iconWrapClass)
                    "
                >
                    <component
                        :is="props.icon"
                        :class="patientConfirmDialogIconClass"
                        aria-hidden="true"
                        :stroke-width="2"
                    />
                </div>
                <h1 :id="titleId" :class="patientActionSuccessTitleClass">
                    {{ title }}
                </h1>
                <p
                    :id="descriptionId"
                    :class="patientActionSuccessSubtitleClass"
                >
                    {{ description }}
                </p>
            </div>

            <div
                :class="[
                    footerRowClass,
                    patientShellWizardFooterClass,
                    'mt-auto shrink-0',
                ]"
            >
                <Button
                    v-if="cancelFirst"
                    type="button"
                    :variant="cancelButtonVariant"
                    size="lg"
                    :class="cancelButtonClass"
                    :disabled="processing"
                    @click="close"
                >
                    {{ cancelLabel }}
                </Button>
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
                    v-if="!cancelFirst"
                    type="button"
                    :variant="cancelButtonVariant"
                    size="lg"
                    :class="cancelButtonClass"
                    :disabled="processing"
                    @click="close"
                >
                    {{ cancelLabel }}
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
