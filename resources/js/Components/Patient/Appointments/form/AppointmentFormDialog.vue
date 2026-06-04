<script setup lang="ts">
import { computed, nextTick, ref, toRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type { AppointmentFormWithErrors } from '@/Components/Patient/Appointments/form/AppointmentFormTypes';
import AppointmentAddressStep from '@/Components/Patient/Appointments/steps/AppointmentAddressStep.vue';
import AppointmentNotesStep from '@/Components/Patient/Appointments/steps/AppointmentNotesStep.vue';
import AppointmentProviderStep from '@/Components/Patient/Appointments/steps/AppointmentProviderStep.vue';
import AppointmentScheduleStep from '@/Components/Patient/Appointments/steps/AppointmentScheduleStep.vue';
import AppointmentTransportStep from '@/Components/Patient/Appointments/steps/AppointmentTransportStep.vue';
import PatientShellWizardScrollBody from '@/Components/Patient/form/PatientShellWizardScrollBody.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { getGoogleMapsApiKey } from '@/lib/google-maps/loadGoogleMapsApi';
import type { AppointmentFormStepId } from '@/lib/patient/appointments/form-wizard/appointmentFormStepGuards';
import {
    APPOINTMENT_FORM_STEP_ORDER,
    appointmentFormStepClientValidatedFieldKeys,
    collectAppointmentFormStepValidationFieldErrors,
    firstAppointmentFormStepContainingFieldErrors,
} from '@/lib/patient/appointments/form-wizard/appointmentFormStepGuards';
import type { PatientAppointmentFormPermitPastStartsAtOptions } from '@/lib/patient/appointments/form-wizard/patientAppointmentDialogFormSchema';
import { getPatientAppointmentDialogFormFieldErrors } from '@/lib/patient/appointments/form-wizard/patientAppointmentDialogFormSchema';
import {
    patientAppointmentFormPrimaryPairButtonClass,
    patientSoftDangerActionButtonClass,
} from '@/lib/patient/appointments/ui/patientSoftDangerActionButtonClass';
import { usePatientFormWizardStepMotion } from '@/composables/motion/usePatientFormWizardStepMotion';
import {
    patientShellDialogOverlayAboveAppChromeClass,
    patientShellWizardFormClass,
} from '@/lib/patient/patientShellDialogLayout';
import type { AppointmentDoctorType } from '@/lib/types';
import {
    applyAppointmentAddressFieldErrors,
    hasAppointmentAddressGeocodeFieldErrors,
} from '@/lib/patient/appointments/applyAppointmentAddressFieldErrors';
import { isAppointmentAddressValidationRequired } from '@/lib/patient/appointments/appointmentAddressValidation';
import { verifyAppointmentAddressGeocode } from '@/lib/patient/appointments/verifyAppointmentAddressGeocode';

const props = defineProps<{
    open: boolean;
    title: string;
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
    startsAtDateInputMinIso?: string | null;
    schedulePermitPastStartsAtIfSameInstantMs?: number | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    submit: [];
    cancel: [];
}>();

const { t } = useI18n();

const step = ref<AppointmentFormStepId>('provider');

const stepOrder = computed(() => {
    if (props.transportFamilies.length > 0) {
        return APPOINTMENT_FORM_STEP_ORDER;
    }

    return APPOINTMENT_FORM_STEP_ORDER.filter((id) => id !== 'transport');
});

const stepSnapshot = computed(() => ({
    doctor_type: props.form.doctor_type,
    provider_name: props.form.provider_name,
    street: props.form.street,
    postal_code: props.form.postal_code,
    city: props.form.city,
    starts_at_date: props.form.starts_at_date,
    starts_at_time: props.form.starts_at_time,
    needs_transport: props.form.needs_transport,
    transport_family_ids: props.form.transport_family_ids,
}));

const currentStepIndex = computed(() => stepOrder.value.indexOf(step.value));

const isOpen = toRef(() => props.open);
const progressLabelRef = ref<HTMLElement | null>(null);

const { wizardStepPanelRef } = usePatientFormWizardStepMotion(
    currentStepIndex,
    isOpen,
    { progressLabelRef },
);

const progressLabel = computed(() =>
    t('patient.appointments.steps.progress', {
        current: currentStepIndex.value + 1,
        total: stepOrder.value.length,
    }),
);

const isNotesStep = computed(() => step.value === 'notes');
const isVerifyingAddress = ref(false);
const addressStepRef = ref<InstanceType<typeof AppointmentAddressStep> | null>(
    null,
);

const isAddressStepBusy = computed(
    () =>
        isVerifyingAddress.value ||
        (addressStepRef.value?.isVerifyingGeocode ?? false),
);

const permitPastStartsAtOptions =
    computed<PatientAppointmentFormPermitPastStartsAtOptions>(() => {
        const ms = props.schedulePermitPastStartsAtIfSameInstantMs;

        if (ms === null || ms === undefined) {
            return {};
        }

        return { permitPastStartsAtIfSameInstantMs: ms };
    });

type LooseInertiaForm = {
    setError: (key: string, value: string) => void;
    clearErrors: (...keys: string[]) => void;
};

function looseInertiaForm(form: AppointmentFormWithErrors): LooseInertiaForm {
    return form as unknown as LooseInertiaForm;
}

function clearClientFieldErrorsForStep(stepId: AppointmentFormStepId): void {
    looseInertiaForm(props.form).clearErrors(
        ...appointmentFormStepClientValidatedFieldKeys(stepId),
    );
}

function applyFieldErrorsToForm(
    fieldErrors: Partial<Record<string, string>>,
): void {
    const target = looseInertiaForm(props.form);

    for (const [rawKey, message] of Object.entries(fieldErrors)) {
        if (message === undefined || message.length < 1) {
            continue;
        }

        target.setError(rawKey, message);
    }
}

function scrollAppointmentFormFirstFieldErrorIntoView(
    stepId: AppointmentFormStepId,
    fieldErrors: Partial<Record<string, string>>,
): void {
    void nextTick(() => {
        const fieldDomSuffix: Record<string, string> = {
            doctor_type: 'doctor-type',
            provider_name: 'provider-name',
            street: 'street',
            house_number: 'house-number',
            postal_code: 'postal-code',
            city: 'city',
            starts_at_date: 'starts-at-date',
            starts_at_time: 'starts-at-time',
            starts_at: 'starts-at-date',
            transport_family_ids: 'needs-transport',
            notes: 'notes',
            status: 'notes',
        };

        const keys = appointmentFormStepClientValidatedFieldKeys(stepId);

        for (const key of keys) {
            const message = fieldErrors[key];

            if (message === undefined || message.length < 1) {
                continue;
            }

            const suffix = fieldDomSuffix[key];

            if (suffix === undefined) {
                continue;
            }

            document
                .getElementById(`${props.idPrefix}-${suffix}`)
                ?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            return;
        }
    });
}

function goNext(): void {
    const order = stepOrder.value;
    const index = order.indexOf(step.value);

    if (index < 0 || index >= order.length - 1) {
        return;
    }

    step.value = order[index + 1]!;
}

function goBack(): void {
    const order = stepOrder.value;
    const index = order.indexOf(step.value);

    if (index <= 0) {
        return;
    }

    step.value = order[index - 1]!;
}

async function tryAdvanceFromCurrentStep(): Promise<void> {
    const activeStep = step.value;

    const fieldErrors = collectAppointmentFormStepValidationFieldErrors(
        activeStep,
        stepSnapshot.value,
        permitPastStartsAtOptions.value,
    );

    const hasFieldErrors = Object.values(fieldErrors).some(
        (message) => message !== undefined && message.length > 0,
    );

    if (hasFieldErrors) {
        clearClientFieldErrorsForStep(activeStep);
        applyFieldErrorsToForm(fieldErrors);
        scrollAppointmentFormFirstFieldErrorIntoView(activeStep, fieldErrors);

        return;
    }

    clearClientFieldErrorsForStep(activeStep);

    if (activeStep === 'address') {
        if (
            !isAppointmentAddressValidationRequired(
                props.form.needs_transport,
                props.form,
            )
        ) {
            goNext();

            return;
        }

        if (hasAppointmentAddressGeocodeFieldErrors(props.form)) {
            scrollAppointmentFormFirstFieldErrorIntoView(activeStep, {
                street: props.form.errors.street,
                postal_code: props.form.errors.postal_code,
                city: props.form.errors.city,
            });

            return;
        }

        if (getGoogleMapsApiKey() !== null) {
            isVerifyingAddress.value = true;

            try {
                const result = await verifyAppointmentAddressGeocode({
                    street: props.form.street,
                    house_number: props.form.house_number,
                    postal_code: props.form.postal_code,
                    city: props.form.city,
                });

                if (!result.valid) {
                    applyAppointmentAddressFieldErrors(
                        props.form,
                        result.fieldErrors,
                    );
                    scrollAppointmentFormFirstFieldErrorIntoView(
                        activeStep,
                        result.fieldErrors,
                    );

                    return;
                }
            } finally {
                isVerifyingAddress.value = false;
            }
        }
    }

    goNext();
}

function handleCancelOrBack(): void {
    if (step.value === 'provider') {
        emit('cancel');

        return;
    }

    goBack();
}

async function handlePrimaryAction(): Promise<void> {
    if (step.value === 'notes') {
        if (props.form.processing) {
            return;
        }

        const fieldErrors = getPatientAppointmentDialogFormFieldErrors(
            {
                doctor_type: props.form.doctor_type,
                provider_name: props.form.provider_name,
                street: props.form.street,
                house_number: props.form.house_number,
                postal_code: props.form.postal_code,
                city: props.form.city,
                starts_at_date: props.form.starts_at_date,
                starts_at_time: props.form.starts_at_time,
                notes: props.form.notes,
                needs_transport: props.form.needs_transport,
                transport_family_ids: props.form.transport_family_ids,
                status: props.form.status,
            },
            permitPastStartsAtOptions.value,
        );

        const hasFieldErrors = Object.values(fieldErrors).some(
            (message) => message !== undefined && message.length > 0,
        );

        if (hasFieldErrors) {
            props.form.clearErrors();
            applyFieldErrorsToForm(fieldErrors);

            const nextStep = firstAppointmentFormStepContainingFieldErrors(
                fieldErrors,
                stepOrder.value,
            );

            if (nextStep !== null) {
                step.value = nextStep;
            }

            scrollAppointmentFormFirstFieldErrorIntoView(
                step.value,
                fieldErrors,
            );

            return;
        }

        props.form.clearErrors();
        emit('submit');

        return;
    }

    await tryAdvanceFromCurrentStep();
}

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            return;
        }

        step.value = 'provider';
    },
);

watch(
    () => props.transportFamilies.length,
    (count) => {
        if (count > 0 || step.value !== 'transport') {
            return;
        }

        step.value = 'schedule';
    },
);

const focusTargetIdByStep: Record<AppointmentFormStepId, string> = {
    provider: 'doctor-type',
    address: 'street',
    schedule: 'starts-at-date',
    transport: 'needs-transport',
    notes: 'notes',
};

watch(
    () => [props.open, step.value] as const,
    async ([isOpen, activeStep]) => {
        if (!isOpen) {
            return;
        }

        await nextTick();

        if (activeStep === 'address') {
            return;
        }

        const suffix = focusTargetIdByStep[activeStep];
        const el = document.getElementById(`${props.idPrefix}-${suffix}`);

        el?.focus({ preventScroll: true });
    },
    { flush: 'post' },
);
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent
            :class="props.dialogContentClass"
            :overlay-class="patientShellDialogOverlayAboveAppChromeClass('sm')"
        >
            <DialogHeader
                class="shrink-0 space-y-2 pt-[env(safe-area-inset-top,0)] text-left sm:space-y-1.5 sm:pt-0"
            >
                <DialogTitle
                    class="text-text-heading text-2xl leading-tight font-bold"
                >
                    {{ props.title }}
                </DialogTitle>
                <DialogDescription
                    ref="progressLabelRef"
                    class="text-text-heading block text-base leading-relaxed font-medium"
                    aria-live="polite"
                >
                    {{ progressLabel }}
                </DialogDescription>
            </DialogHeader>

            <form
                :id="props.formId"
                :class="patientShellWizardFormClass"
                novalidate
                @submit.prevent="handlePrimaryAction"
            >
                <PatientShellWizardScrollBody
                    :active="props.open"
                    :step-key="step"
                >
                    <div ref="wizardStepPanelRef" class="space-y-3 sm:space-y-4">
                        <Card
                            class="border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] sm:rounded-3xl"
                        >
                            <CardContent class="p-0">
                                <div
                                    class="bg-surface space-y-5 rounded-2xl px-4 py-4 sm:space-y-6 sm:rounded-3xl sm:px-5 sm:py-5 md:p-7 lg:p-8"
                                >
                                    <AppointmentProviderStep
                                        v-if="step === 'provider'"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                        :doctor-type-values="
                                            props.doctorTypeValues
                                        "
                                        :show-doctor-type-placeholder="
                                            props.showDoctorTypePlaceholder
                                        "
                                    />

                                    <AppointmentAddressStep
                                        ref="addressStepRef"
                                        v-if="step === 'address'"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />

                                    <AppointmentScheduleStep
                                        v-if="step === 'schedule'"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                        :starts-at-date-input-min-iso="
                                            props.startsAtDateInputMinIso ??
                                            null
                                        "
                                    />

                                    <AppointmentTransportStep
                                        v-if="step === 'transport'"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                        :transport-families="
                                            props.transportFamilies
                                        "
                                    />

                                    <AppointmentNotesStep
                                        v-if="step === 'notes'"
                                        :form="props.form"
                                        :id-prefix="props.idPrefix"
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <template #footer>
                        <div
                            class="flex w-full min-w-0 flex-col gap-2 sm:flex-row-reverse sm:gap-3"
                        >
                            <Button
                                v-if="!isNotesStep"
                                type="button"
                                variant="default"
                                size="lg"
                                :disabled="isAddressStepBusy"
                                :class="
                                    patientAppointmentFormPrimaryPairButtonClass
                                "
                                @click.stop.prevent="tryAdvanceFromCurrentStep"
                            >
                                {{ t('patient.appointments.steps.continue') }}
                            </Button>

                            <Button
                                v-else
                                type="submit"
                                variant="default"
                                size="lg"
                                :disabled="props.form.processing"
                                :class="
                                    patientAppointmentFormPrimaryPairButtonClass
                                "
                            >
                                {{ t('patient.appointments.actions.save') }}
                            </Button>

                            <Button
                                type="button"
                                variant="secondary"
                                size="lg"
                                :disabled="props.form.processing"
                                :class="patientSoftDangerActionButtonClass"
                                @click.stop.prevent="handleCancelOrBack"
                            >
                                {{
                                    step === 'provider'
                                        ? t('patient.appointments.actions.cancel')
                                        : t('patient.appointments.steps.back')
                                }}
                            </Button>
                        </div>
                    </template>
                </PatientShellWizardScrollBody>
            </form>
        </DialogContent>
    </Dialog>
</template>
