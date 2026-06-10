<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import MobileShellFormDialog from '@/Components/shell/MobileShellFormDialog.vue';
import { Button } from '@/Components/ui/button';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import {
    mobileShellFormFieldInvalidClass,
    mobileShellFormLabelClass,
    mobileShellFormNativeDateTimeInputClass,
} from '@/lib/shell/mobileShellFormFieldClasses';
import {
    mobileShellFormWizardFooterCancelButtonClass,
    mobileShellFormWizardFooterPrimaryButtonClass,
    mobileShellFormWizardFooterRowClass,
} from '@/lib/shell/mobileShellDialogLayout';
import type { MedicationPrescriptionItem } from '@/lib/types';
import { cn } from '@/lib/utils';

const props = defineProps<{
    open: boolean;
    prescription: MedicationPrescriptionItem;
    formId: string;
    idPrefix: string;
    dialogContentClass: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const { t } = useI18n();

const form = useForm({
    prescription_expiry_date: '',
});

const expiryDateLabel = computed((): string => {
    if (props.prescription.is_last_in_batch) {
        return t('patient.prescriptions.expiryDateLastPrescriptionLabel', {
            number: '1',
        });
    }

    return t('patient.prescriptions.expiryDateLabel', {
        number: '1',
    });
});

watch(
    () => props.open,
    (open) => {
        if (!open) {
            return;
        }

        form.defaults({
            prescription_expiry_date:
                props.prescription.prescription_expiry_date ?? '',
        });
        form.reset();
        form.clearErrors();
    },
);

function close(): void {
    emit('update:open', false);
}

function submit(): void {
    form.patch(route('patient.prescriptions.update', props.prescription.id), {
        preserveScroll: true,
        onSuccess: () => {
            close();
            emit('saved');
        },
    });
}
</script>

<template>
    <MobileShellFormDialog
        :open="props.open"
        :title="t('patient.prescriptions.dialogEditTitle')"
        :form-id="props.formId"
        :dialog-content-class="props.dialogContentClass"
        @update:open="emit('update:open', $event)"
        @submit="submit"
        @cancel="close"
    >
        <div class="min-w-0 space-y-2">
            <Label
                :for="`${props.idPrefix}-expiry-date`"
                :class="mobileShellFormLabelClass"
            >
                {{ expiryDateLabel }}
                <span class="text-danger"> *</span>
            </Label>

            <input
                :id="`${props.idPrefix}-expiry-date`"
                v-model="form.prescription_expiry_date"
                type="date"
                required
                :class="
                    cn(
                        mobileShellFormNativeDateTimeInputClass,
                        form.errors.prescription_expiry_date &&
                            mobileShellFormFieldInvalidClass,
                    )
                "
            />

            <InputError
                :id="`${props.idPrefix}-expiry-date-error`"
                :message="form.errors.prescription_expiry_date"
            />
        </div>

        <template #footer>
            <div :class="mobileShellFormWizardFooterRowClass">
                <Button
                    type="button"
                    variant="secondary"
                    size="lg"
                    :disabled="form.processing"
                    :class="mobileShellFormWizardFooterCancelButtonClass"
                    @click="close"
                >
                    {{ t('patient.prescriptions.actions.cancel') }}
                </Button>
                <Button
                    type="submit"
                    variant="default"
                    size="lg"
                    :disabled="form.processing"
                    :class="mobileShellFormWizardFooterPrimaryButtonClass"
                >
                    {{ t('patient.prescriptions.actions.save') }}
                </Button>
            </div>
        </template>
    </MobileShellFormDialog>
</template>
