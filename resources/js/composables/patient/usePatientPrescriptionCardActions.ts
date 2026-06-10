import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { MedicationPrescriptionItem } from '@/lib/types';

export function usePatientPrescriptionCardActions() {
    const editDialogOpen = ref(false);
    const prescriptionPendingEdit = ref<MedicationPrescriptionItem | null>(
        null,
    );

    const deleteDialogOpen = ref(false);
    const prescriptionPendingDelete = ref<MedicationPrescriptionItem | null>(
        null,
    );
    const deleteProcessing = ref(false);

    function openEditPrescriptionDialog(
        prescription: MedicationPrescriptionItem,
    ): void {
        prescriptionPendingEdit.value = prescription;
        editDialogOpen.value = true;
    }

    function closeEditPrescriptionDialog(): void {
        editDialogOpen.value = false;
        prescriptionPendingEdit.value = null;
    }

    function openDeletePrescriptionDialog(
        prescription: MedicationPrescriptionItem,
    ): void {
        prescriptionPendingDelete.value = prescription;
        deleteDialogOpen.value = true;
    }

    function closeDeletePrescriptionDialog(): void {
        deleteDialogOpen.value = false;
        prescriptionPendingDelete.value = null;
    }

    function confirmDeletePrescription(): void {
        const prescription = prescriptionPendingDelete.value;

        if (prescription === null) {
            return;
        }

        deleteProcessing.value = true;

        router.delete(route('patient.prescriptions.destroy', prescription.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeDeletePrescriptionDialog();
            },
            onFinish: () => {
                deleteProcessing.value = false;
            },
        });
    }

    return {
        editDialogOpen,
        prescriptionPendingEdit,
        openEditPrescriptionDialog,
        closeEditPrescriptionDialog,
        deleteDialogOpen,
        prescriptionPendingDelete,
        deleteProcessing,
        openDeletePrescriptionDialog,
        closeDeletePrescriptionDialog,
        confirmDeletePrescription,
    };
}
