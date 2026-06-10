<script setup lang="ts">
import { computed, toRef, useSlots } from 'vue';
import MobileShellWizardScrollBody from '@/Components/shell/MobileShellWizardScrollBody.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { useMobileShellDialogChromeSync } from '@/composables/patient/useMobileShellDialogChrome';
import {
    mobileShellDialogContentClass,
    mobileShellDialogOverlayAboveAppChromeClass,
    mobileShellDialogDescriptionClass,
    mobileShellDialogHeaderClass,
    mobileShellDialogTitleClass,
    mobileShellWizardFormClass,
} from '@/lib/shell/mobileShellDialogLayout';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description?: string;
        formId: string;
        desktopFrom?: 'sm' | 'md';
        dialogContentClass?: string;
        stepKey?: string | number;
        centered?: boolean;
    }>(),
    {
        desktopFrom: 'md',
        centered: false,
    },
);

const emit = defineEmits<{
    'update:open': [value: boolean];
    submit: [];
    cancel: [];
}>();

const slots = useSlots();

const hasDescription = computed(
    () =>
        (props.description !== undefined && props.description !== '') ||
        slots.description !== undefined,
);

/** Suppress reka-ui dev warning when this dialog intentionally has no description. */
const dialogContentA11yBindings = computed(() =>
    hasDescription.value ? {} : { 'aria-describedby': undefined },
);

const resolvedDialogContentClass = computed(
    () =>
        props.dialogContentClass ??
        mobileShellDialogContentClass(props.desktopFrom),
);

const overlayClass = computed(() =>
    mobileShellDialogOverlayAboveAppChromeClass(props.desktopFrom),
);

useMobileShellDialogChromeSync(toRef(() => props.open));

function onDialogOpenChange(value: boolean): void {
    emit('update:open', value);

    if (!value) {
        emit('cancel');
    }
}
</script>

<template>
    <Dialog :open="props.open" @update:open="onDialogOpenChange">
        <DialogContent
            v-bind="dialogContentA11yBindings"
            :class="resolvedDialogContentClass"
            :overlay-class="overlayClass"
        >
            <DialogHeader
                :class="
                    cn(
                        mobileShellDialogHeaderClass,
                        props.centered && 'text-center',
                    )
                "
            >
                <DialogTitle
                    :class="
                        cn(
                            mobileShellDialogTitleClass,
                            props.centered && 'text-center',
                        )
                    "
                >
                    {{ props.title }}
                </DialogTitle>
                <slot name="description">
                    <DialogDescription
                        v-if="
                            props.description !== undefined &&
                            props.description !== ''
                        "
                        :class="
                            cn(
                                mobileShellDialogDescriptionClass,
                                props.centered && 'text-center',
                            )
                        "
                    >
                        {{ props.description }}
                    </DialogDescription>
                </slot>
            </DialogHeader>

            <form
                :id="props.formId"
                :class="mobileShellWizardFormClass"
                novalidate
                @submit.prevent="emit('submit')"
            >
                <MobileShellWizardScrollBody
                    :active="props.open"
                    :step-key="props.stepKey"
                >
                    <slot />

                    <template #footer>
                        <slot name="footer" />
                    </template>
                </MobileShellWizardScrollBody>
            </form>
        </DialogContent>
    </Dialog>
</template>
