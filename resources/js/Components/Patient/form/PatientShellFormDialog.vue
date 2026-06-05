<script setup lang="ts">
import { computed } from 'vue';
import PatientShellWizardScrollBody from '@/Components/Patient/form/PatientShellWizardScrollBody.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import {
    patientShellDialogContentClass,
    patientShellDialogOverlayAboveAppChromeClass,
    patientShellPageDescriptionClass,
    patientShellPageHeaderClass,
    patientShellPageTitleClass,
    patientShellWizardFormClass,
} from '@/lib/patient/patientShellDialogLayout';
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

const resolvedDialogContentClass = computed(
    () =>
        props.dialogContentClass ??
        patientShellDialogContentClass(props.desktopFrom),
);

const overlayClass = computed(() =>
    patientShellDialogOverlayAboveAppChromeClass(props.desktopFrom),
);

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
            :class="resolvedDialogContentClass"
            :overlay-class="overlayClass"
        >
            <DialogHeader
                :class="
                    cn(
                        patientShellPageHeaderClass,
                        props.centered && 'text-center',
                    )
                "
            >
                <DialogTitle
                    :class="
                        cn(
                            patientShellPageTitleClass,
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
                                patientShellPageDescriptionClass,
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
                :class="patientShellWizardFormClass"
                novalidate
                @submit.prevent="emit('submit')"
            >
                <PatientShellWizardScrollBody
                    :active="props.open"
                    :step-key="props.stepKey"
                >
                    <slot />

                    <template #footer>
                        <slot name="footer" />
                    </template>
                </PatientShellWizardScrollBody>
            </form>
        </DialogContent>
    </Dialog>
</template>
