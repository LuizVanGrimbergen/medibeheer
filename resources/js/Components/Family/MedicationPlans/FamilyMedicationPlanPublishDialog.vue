<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientFormWizardFooter from '@/Components/Patient/form/PatientFormWizardFooter.vue';
import PatientFormWizardFooterButton from '@/Components/Patient/form/PatientFormWizardFooterButton.vue';
import MobileShellFormDialog from '@/Components/shell/MobileShellFormDialog.vue';
import MobileShellWizardCard from '@/Components/shell/MobileShellWizardCard.vue';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { useMobileShellDialogLayout } from '@/composables/patient/useMobileShellDialogLayout';
import {
    mobileShellFormFieldInputClass,
    mobileShellFormFieldInvalidClass,
    mobileShellFormLabelClass,
} from '@/lib/shell/mobileShellFormFieldClasses';
import { mobileShellWizardStepPanelClass } from '@/lib/shell/mobileShellDialogLayout';
import { cn } from '@/lib/utils';

const props = defineProps<{
    formId: string;
    cancelUrl: string;
    patientEmail: string;
    emailError: string | undefined;
    processing: boolean;
}>();

const emit = defineEmits<{
    'update:patientEmail': [value: string];
    submit: [];
}>();

const { t } = useI18n();
const { dialogContentClass } = useMobileShellDialogLayout('md');

const open = ref(true);

function onCancel(): void {
    router.visit(props.cancelUrl);
}
</script>

<template>
    <MobileShellFormDialog
        :open="open"
        :title="t('family.medicationPlans.publishPage.title')"
        :form-id="props.formId"
        :dialog-content-class="dialogContentClass"
        step-key="publish"
        @update:open="open = $event"
        @submit="emit('submit')"
        @cancel="onCancel"
    >
        <div :class="mobileShellWizardStepPanelClass">
            <MobileShellWizardCard>
                <p class="text-text-muted text-sm leading-relaxed md:text-base">
                    {{ t('family.medicationPlans.publishPage.hint') }}
                </p>

                <div class="flex flex-col gap-2">
                    <Label
                        for="medication-plan-patient-email"
                        :class="mobileShellFormLabelClass"
                    >
                        {{ t('family.medicationPlans.publishPage.emailLabel') }}
                    </Label>
                    <Input
                        id="medication-plan-patient-email"
                        :model-value="patientEmail"
                        type="email"
                        autocomplete="email"
                        inputmode="email"
                        :class="
                            cn(
                                mobileShellFormFieldInputClass,
                                emailError
                                    ? mobileShellFormFieldInvalidClass
                                    : null,
                            )
                        "
                        :placeholder="
                            t(
                                'family.medicationPlans.publishPage.emailPlaceholder',
                            )
                        "
                        :aria-invalid="Boolean(emailError)"
                        @update:model-value="
                            emit('update:patientEmail', String($event))
                        "
                    />
                    <InputError class="mt-1" :message="emailError" />
                </div>
            </MobileShellWizardCard>
        </div>

        <template #footer>
            <PatientFormWizardFooter>
                <PatientFormWizardFooterButton
                    variant="primary"
                    type="submit"
                    :disabled="processing"
                >
                    {{ t('family.medicationPlans.publishPage.submit') }}
                </PatientFormWizardFooterButton>
                <PatientFormWizardFooterButton
                    variant="outline"
                    :href="props.cancelUrl"
                >
                    {{ t('family.medicationPlans.publishPage.cancel') }}
                </PatientFormWizardFooterButton>
            </PatientFormWizardFooter>
        </template>
    </MobileShellFormDialog>
</template>
