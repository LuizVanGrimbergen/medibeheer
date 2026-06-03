import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePrescriptionFormWizard } from '@/Components/Patient/Prescriptions/form/usePrescriptionFormWizard';
import type { PatientActionSuccessDetail } from '@/composables/usePatientActionSuccessScreen';
import { usePatientActionSuccessScreen } from '@/composables/usePatientActionSuccessScreen';
import { patientShellDialogContentClass } from '@/lib/patient/patientShellDialogLayout';
import type { PatientPrescriptionForm } from '@/lib/patient/prescriptions/patientPrescriptionFormTypes';
import type { PatientPrescriptionMedicationChoice } from '@/lib/patient/prescriptions/patientPrescriptionsScreenProps';

const prescriptionFormDialogLayoutClass = patientShellDialogContentClass('md');

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
    const addDialogOpen = ref(false);
    const selectedMedicationId = ref<number | null>(null);
    const quantityClientError = ref('');
    const medicationClientError = ref('');
    const expiryDatesClientError = ref('');

    const form: PatientPrescriptionForm = useForm({
        quantity: PRESCRIPTION_QUANTITY_MIN,
        prescription_expiry_dates: [''],
    });

    const canAddPrescription = computed(() => medicationChoices().length > 0);

    watch(
        () => form.quantity,
        (rawQuantity) => {
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

    watch(addDialogOpen, (open) => {
        if (!open) {
            form.clearErrors();
            quantityClientError.value = '';
            medicationClientError.value = '';
            expiryDatesClientError.value = '';
        }
    });

    function openAddPrescriptionDialog(): void {
        const choices = medicationChoices();
        const firstMedicationId = choices[0]?.id ?? null;

        selectedMedicationId.value = firstMedicationId;
        quantityClientError.value = '';
        medicationClientError.value = '';
        expiryDatesClientError.value = '';
        form.defaults({
            quantity: PRESCRIPTION_QUANTITY_MIN,
            prescription_expiry_dates: [''],
        });
        form.reset();
        form.clearErrors();
        addDialogOpen.value = true;
    }

    function closeAddPrescriptionDialog(): void {
        addDialogOpen.value = false;
    }

    function submitAddPrescription(): void {
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

        const medicationName =
            medicationChoices()
                .find((choice) => choice.id === medicationId)
                ?.name?.trim() ?? '';
        const successDetails: PatientActionSuccessDetail[] = [];

        if (medicationName !== '') {
            successDetails.push({
                label: t('patient.actionSuccess.summary.medication'),
                value: medicationName,
            });
        }

        successDetails.push({
            label: t('patient.actionSuccess.summary.quantity'),
            value: String(parsedQuantity),
        });

        form.post(
            route('patient.medications.prescriptions.store', {
                medication: medicationId,
            }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    closeAddPrescriptionDialog();
                    addSuccessScreen.show({
                        title: t(
                            'patient.actionSuccess.prescriptions.created.title',
                        ),
                        message: t(
                            'patient.actionSuccess.prescriptions.created.message',
                        ),
                        details: successDetails,
                    });
                },
                onError: () => {
                    addDialogOpen.value = true;
                },
            },
        );
    }

    const {
        currentStep,
        progressLabel,
        handleSubmit: handlePrescriptionDialogSubmit,
        handleBackOrCancel: handlePrescriptionDialogBackOrCancel,
    } = usePrescriptionFormWizard({
        open: addDialogOpen,
        form,
        selectedMedicationId,
        quantityClientError,
        medicationClientError,
        expiryDatesClientError,
        onSubmit: submitAddPrescription,
        onCancel: closeAddPrescriptionDialog,
    });

    return {
        prescriptionFormDialogLayoutClass,
        addSuccessOpen: addSuccessScreen.open,
        addSuccessTitle: addSuccessScreen.title,
        addSuccessMessage: addSuccessScreen.message,
        addSuccessDetails: addSuccessScreen.details,
        addDialogOpen,
        selectedMedicationId,
        quantityClientError,
        medicationClientError,
        expiryDatesClientError,
        canAddPrescription,
        form,
        currentStep,
        progressLabel,
        openAddPrescriptionDialog,
        closeAddPrescriptionDialog,
        handlePrescriptionDialogSubmit,
        handlePrescriptionDialogBackOrCancel,
    };
}
