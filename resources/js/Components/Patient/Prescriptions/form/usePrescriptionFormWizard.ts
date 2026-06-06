import type { Ref } from 'vue';
import { computed, nextTick, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import type { PatientPrescriptionForm } from '@/lib/patient/prescriptions/patientPrescriptionFormTypes';
import type { PrescriptionFormWizardStep } from '@/lib/patient/prescriptions/prescriptionFormWizardTypes';
import { PRESCRIPTION_FORM_WIZARD_STEP_COUNT } from '@/lib/patient/prescriptions/prescriptionFormWizardTypes';

const PRESCRIPTION_QUANTITY_MIN = 1;
const PRESCRIPTION_QUANTITY_MAX = 24;

function clampPrescriptionQuantity(raw: number): number {
    if (!Number.isFinite(raw)) {
        return PRESCRIPTION_QUANTITY_MIN;
    }

    return Math.min(
        PRESCRIPTION_QUANTITY_MAX,
        Math.max(PRESCRIPTION_QUANTITY_MIN, Math.trunc(raw)),
    );
}

export function usePrescriptionFormWizard(options: {
    open: Ref<boolean>;
    form: PatientPrescriptionForm;
    selectedMedicationId: Ref<number | null>;
    idPrefix: Ref<string>;
    quantityClientError: Ref<string>;
    medicationClientError: Ref<string>;
    expiryDatesClientError: Ref<string>;
    onSubmit: () => void;
    onCancel: () => void;
}) {
    const { t } = useI18n();
    const currentStep = ref<PrescriptionFormWizardStep>(1);

    const progressLabel = computed(() =>
        t('patient.prescriptions.stepsProgress', {
            current: currentStep.value,
            total: PRESCRIPTION_FORM_WIZARD_STEP_COUNT,
        }),
    );

    watch(options.open, (isOpen) => {
        if (!isOpen) {
            return;
        }

        currentStep.value = 1;
    });

    watch(
        () => options.form.errors,
        (errors) => {
            if (!options.open.value) {
                return;
            }

            if (errors.quantity !== undefined) {
                currentStep.value = 1;

                return;
            }

            const hasExpiryFieldError = Object.keys(errors).some(
                (key) =>
                    key === 'prescription_expiry_dates' ||
                    key.startsWith('prescription_expiry_dates.'),
            );

            if (hasExpiryFieldError) {
                currentStep.value = 2;
            }
        },
    );

    function validateDetailsStep(): boolean {
        options.quantityClientError.value = '';
        options.medicationClientError.value = '';

        const rawQuantity = options.form.quantity;

        if (rawQuantity === null) {
            options.quantityClientError.value =
                'patient.prescriptions.quantityInvalid';

            return false;
        }

        const parsedQuantity = clampPrescriptionQuantity(rawQuantity);

        if (
            !Number.isFinite(rawQuantity) ||
            parsedQuantity < PRESCRIPTION_QUANTITY_MIN ||
            parsedQuantity > PRESCRIPTION_QUANTITY_MAX
        ) {
            options.quantityClientError.value =
                'patient.prescriptions.quantityInvalid';

            return false;
        }

        options.form.quantity = parsedQuantity;

        const medicationId = options.selectedMedicationId.value;

        if (medicationId === null || !Number.isFinite(medicationId)) {
            options.medicationClientError.value =
                'patient.prescriptions.medicationRequired';

            return false;
        }

        return true;
    }

    function validateExpiryDatesStep(): boolean {
        options.expiryDatesClientError.value = '';

        const hasEmptyExpiryDate = options.form.prescription_expiry_dates.some(
            (date) => date.trim().length < 1,
        );

        if (hasEmptyExpiryDate) {
            options.expiryDatesClientError.value =
                'patient.prescriptions.expiryDatesRequired';

            return false;
        }

        return true;
    }

    function handleSubmit(): void {
        if (options.form.processing) {
            return;
        }

        if (currentStep.value === 1) {
            if (!validateDetailsStep()) {
                return;
            }

            currentStep.value = 2;

            return;
        }

        if (currentStep.value === 2) {
            if (!validateExpiryDatesStep()) {
                return;
            }

            currentStep.value = 3;

            void nextTick(() => {
                document
                    .getElementById(
                        `${options.idPrefix.value}-create-summary-title`,
                    )
                    ?.focus({ preventScroll: true });
            });

            return;
        }

        options.onSubmit();
    }

    function handleBackOrCancel(): void {
        if (options.form.processing) {
            return;
        }

        if (currentStep.value === 1) {
            options.onCancel();

            return;
        }

        if (currentStep.value === 2) {
            options.expiryDatesClientError.value = '';
            options.form.clearErrors();
            currentStep.value = 1;

            return;
        }

        currentStep.value = 2;
    }

    function goToPrescriptionWizardStepFromSummary(
        step: PrescriptionFormWizardStep,
        focusElementIdSuffix?: string,
    ): void {
        if (
            !options.open.value ||
            options.form.processing ||
            currentStep.value !== 3
        ) {
            return;
        }

        options.form.clearErrors();
        currentStep.value = step;

        void nextTick(() => {
            if (
                focusElementIdSuffix !== undefined &&
                focusElementIdSuffix.length > 0
            ) {
                document
                    .getElementById(
                        `${options.idPrefix.value}-${focusElementIdSuffix}`,
                    )
                    ?.focus({ preventScroll: true });

                return;
            }

            const defaultFocusSuffix =
                step === 1 ? 'medication' : 'expiry-0';

            document
                .getElementById(
                    `${options.idPrefix.value}-${defaultFocusSuffix}`,
                )
                ?.focus({ preventScroll: true });
        });
    }

    return {
        currentStep,
        progressLabel,
        handleSubmit,
        handleBackOrCancel,
        goToPrescriptionWizardStepFromSummary,
    };
}
