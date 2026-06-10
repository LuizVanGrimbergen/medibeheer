import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePrescriptionFormWizard } from '@/Components/Patient/Prescriptions/form/usePrescriptionFormWizard';
import { usePatientActionSuccessScreen } from '@/composables/patient/usePatientActionSuccessScreen';
import { mobileShellDialogContentClass } from '@/lib/shell/mobileShellDialogLayout';
import type { PatientPrescriptionForm } from '@/lib/patient/prescriptions/patientPrescriptionFormTypes';
import type { PatientPrescriptionMedicationChoice } from '@/lib/patient/prescriptions/patientPrescriptionsScreenProps';

const prescriptionFormDialogLayoutClass = mobileShellDialogContentClass('md');
const prescriptionFormIdPrefix = 'patient-prescription-form';

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

function resizePrescriptionExpiryDates(
    dates: string[],
    targetLength: number,
): string[] {
    const next = [...dates];

    while (next.length < targetLength) {
        next.push('');
    }

    return next.slice(0, targetLength);
}

export function usePatientPrescriptionsPage(
    medicationChoices: () => PatientPrescriptionMedicationChoice[],
) {
    const { t } = useI18n();
    const addSuccessScreen = usePatientActionSuccessScreen();
    const pickupSuccessScreen = usePatientActionSuccessScreen();
    const pickupSuccessIsLastPrescription = ref(false);
    const prescriptionFormDialogOpen = ref(false);
    const selectedMedicationId = ref<number | null>(null);
    const quantityClientError = ref('');
    const medicationClientError = ref('');
    const expiryDatesClientError = ref('');

    const form: PatientPrescriptionForm = useForm({
        quantity: null,
        prescription_expiry_dates: [],
    });

    const canOpenPrescriptionForm = computed(
        () => medicationChoices().length > 0,
    );

    watch(
        () => form.quantity,
        (rawQuantity) => {
            if (rawQuantity === null) {
                form.prescription_expiry_dates = [];

                return;
            }

            const count = clampPrescriptionQuantity(rawQuantity);

            if (count !== rawQuantity) {
                form.quantity = count;
            }

            form.prescription_expiry_dates = resizePrescriptionExpiryDates(
                form.prescription_expiry_dates,
                count,
            );
        },
    );

    watch(prescriptionFormDialogOpen, (open) => {
        if (!open) {
            form.clearErrors();
            quantityClientError.value = '';
            medicationClientError.value = '';
            expiryDatesClientError.value = '';
        }
    });

    function openPrescriptionFormDialog(): void {
        selectedMedicationId.value = null;
        quantityClientError.value = '';
        medicationClientError.value = '';
        expiryDatesClientError.value = '';
        form.defaults({
            quantity: null,
            prescription_expiry_dates: [],
        });
        form.reset();
        form.clearErrors();
        prescriptionFormDialogOpen.value = true;
    }

    function closePrescriptionFormDialog(): void {
        prescriptionFormDialogOpen.value = false;
    }

    function submitPrescriptionForm(): void {
        if (form.quantity === null) {
            return;
        }

        const parsedQuantity = clampPrescriptionQuantity(form.quantity);

        form.quantity = parsedQuantity;
        form.prescription_expiry_dates = resizePrescriptionExpiryDates(
            form.prescription_expiry_dates,
            parsedQuantity,
        );

        const medicationId = selectedMedicationId.value;

        if (medicationId === null || !Number.isFinite(medicationId)) {
            return;
        }

        form.post(
            route('patient.medications.prescriptions.store', {
                medication: medicationId,
            }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    closePrescriptionFormDialog();
                    addSuccessScreen.show({
                        title: t(
                            'patient.actionSuccess.prescriptions.created.title',
                        ),
                    });
                },
                onError: () => {
                    prescriptionFormDialogOpen.value = true;
                },
            },
        );
    }

    const {
        currentStep,
        progressLabel,
        handleSubmit: handlePrescriptionDialogSubmit,
        handleBackOrCancel: handlePrescriptionDialogBackOrCancel,
        goToPrescriptionWizardStepFromSummary,
    } = usePrescriptionFormWizard({
        open: prescriptionFormDialogOpen,
        form,
        selectedMedicationId,
        idPrefix: ref(prescriptionFormIdPrefix),
        quantityClientError,
        medicationClientError,
        expiryDatesClientError,
        onSubmit: submitPrescriptionForm,
        onCancel: closePrescriptionFormDialog,
    });

    function showPrescriptionPickedUpSuccess(isLastInBatch: boolean): void {
        pickupSuccessIsLastPrescription.value = isLastInBatch;
        pickupSuccessScreen.show({
            title: t('patient.actionSuccess.prescriptions.pickedUp.title'),
        });
    }

    return {
        prescriptionFormDialogLayoutClass,
        addSuccessOpen: addSuccessScreen.open,
        addSuccessTitle: addSuccessScreen.title,
        pickupSuccessOpen: pickupSuccessScreen.open,
        pickupSuccessIsLastPrescription,
        showPrescriptionPickedUpSuccess,
        prescriptionFormDialogOpen,
        selectedMedicationId,
        quantityClientError,
        medicationClientError,
        expiryDatesClientError,
        canOpenPrescriptionForm,
        form,
        currentStep,
        progressLabel,
        openPrescriptionFormDialog,
        closePrescriptionFormDialog,
        handlePrescriptionDialogSubmit,
        handlePrescriptionDialogBackOrCancel,
        goToPrescriptionWizardStepFromSummary,
    };
}
