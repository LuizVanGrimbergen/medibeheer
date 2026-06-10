<script setup lang="ts">
import type { Component } from 'vue';
import { computed, toRef } from 'vue';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/Components/ui/dialog';
import { useMobileShellDialogChromeSync } from '@/composables/patient/useMobileShellDialogChrome';
import {
    mobileShellActionSuccessSubtitleClass,
    mobileShellActionSuccessTitleClass,
} from '@/lib/shell/mobileShellTypography';
import {
    mobileShellConfirmDialogContentClass,
    mobileShellConfirmDialogIconClass,
    mobileShellConfirmDialogIconWrapClass,
    mobileShellConfirmDialogIconWrapDangerClass,
    mobileShellConfirmDialogIconWrapPrimaryClass,
    mobileShellConfirmDialogMessageClass,
    mobileShellFormWizardFooterCancelButtonClass,
    mobileShellFormWizardFooterOutlineButtonClass,
    mobileShellFormWizardFooterPrimaryButtonClass,
    mobileShellFormWizardFooterRowClass,
    mobileShellDialogOverlayAboveAppChromeClass,
    mobileShellWizardFooterClass,
} from '@/lib/shell/mobileShellDialogLayout';
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

useMobileShellDialogChromeSync(toRef(() => props.open));

const iconWrapClass = computed(() =>
    props.iconTone === 'primary'
        ? mobileShellConfirmDialogIconWrapPrimaryClass
        : mobileShellConfirmDialogIconWrapDangerClass,
);

const confirmButtonClass = computed(() =>
    props.confirmTone === 'primary'
        ? mobileShellFormWizardFooterPrimaryButtonClass
        : mobileShellFormWizardFooterCancelButtonClass,
);

const confirmButtonVariant = computed(() =>
    props.confirmTone === 'primary' ? 'default' : 'secondary',
);

const footerRowClass = computed(() =>
    props.cancelFirst
        ? 'flex w-full min-w-0 flex-col gap-2 md:flex-row md:gap-3'
        : mobileShellFormWizardFooterRowClass,
);

const cancelButtonClass = computed(() =>
    props.cancelTone === 'primary'
        ? mobileShellFormWizardFooterPrimaryButtonClass
        : mobileShellFormWizardFooterOutlineButtonClass,
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
                cn(mobileShellConfirmDialogContentClass, 'flex min-h-0 flex-col')
            "
            :overlay-class="mobileShellDialogOverlayAboveAppChromeClass('md')"
        >
            <div :class="mobileShellConfirmDialogMessageClass">
                <div
                    v-if="props.icon !== undefined"
                    :class="
                        cn(mobileShellConfirmDialogIconWrapClass, iconWrapClass)
                    "
                >
                    <component
                        :is="props.icon"
                        :class="mobileShellConfirmDialogIconClass"
                        aria-hidden="true"
                        :stroke-width="2"
                    />
                </div>
                <DialogTitle :class="mobileShellActionSuccessTitleClass">
                    {{ title }}
                </DialogTitle>
                <DialogDescription
                    :class="mobileShellActionSuccessSubtitleClass"
                >
                    {{ description }}
                </DialogDescription>
            </div>

            <div
                :class="[
                    footerRowClass,
                    mobileShellWizardFooterClass,
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
