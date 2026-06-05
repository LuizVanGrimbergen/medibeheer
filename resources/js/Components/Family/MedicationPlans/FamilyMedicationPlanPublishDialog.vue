<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientFormWizardFooter from '@/Components/Patient/form/PatientFormWizardFooter.vue';
import PatientFormWizardFooterButton from '@/Components/Patient/form/PatientFormWizardFooterButton.vue';
import PatientShellFormDialog from '@/Components/Patient/form/PatientShellFormDialog.vue';
import PatientShellWizardCard from '@/Components/Patient/form/PatientShellWizardCard.vue';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { usePatientShellDialogLayout } from '@/composables/patient/usePatientShellDialogLayout';
import {
    patientFormFieldInputClass,
    patientFormFieldInvalidClass,
    patientFormLabelClass,
} from '@/lib/patient/patientFormFieldClasses';
import { patientShellWizardStepPanelClass } from '@/lib/patient/patientShellDialogLayout';
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
const { dialogContentClass } = usePatientShellDialogLayout('md');

const open = ref(true);

function onCancel(): void {
    router.visit(props.cancelUrl);
}
</script>

<template>
    <PatientShellFormDialog
        :open="open"
        :title="t('family.medicationPlans.publishPage.title')"
        :form-id="props.formId"
        :dialog-content-class="dialogContentClass"
        step-key="publish"
        @update:open="open = $event"
        @submit="emit('submit')"
        @cancel="onCancel"
    >
        <div :class="patientShellWizardStepPanelClass">
            <PatientShellWizardCard>
                <p class="text-text-muted text-sm leading-relaxed md:text-base">
                    {{ t('family.medicationPlans.publishPage.hint') }}
                </p>

                <div class="flex flex-col gap-2">
                    <Label
                        for="medication-plan-patient-email"
                        :class="patientFormLabelClass"
                    >
                        {{
                            t(
                                'family.medicationPlans.publishPage.emailLabel',
                            )
                        }}
                    </Label>
                    <Input
                        id="medication-plan-patient-email"
                        :model-value="patientEmail"
                        type="email"
                        autocomplete="email"
                        inputmode="email"
                        :class="
                            cn(
                                patientFormFieldInputClass,
                                emailError
                                    ? patientFormFieldInvalidClass
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
            </PatientShellWizardCard>
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
    </PatientShellFormDialog>
</template>
