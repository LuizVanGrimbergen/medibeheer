import type { RequestPayload } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { MedicationPrescriptionPickupStatusValue } from '@/lib/types';

type PatientPrescriptionCompleteActionsOptions = {
    onPickedUp?: () => void;
};

export function usePatientPrescriptionCompleteActions(
    options: PatientPrescriptionCompleteActionsOptions = {},
) {
    const prescriptionIdsAwaitingResponse = ref<number[]>([]);

    function isPrescriptionUpdateInFlight(prescriptionId: number): boolean {
        return prescriptionIdsAwaitingResponse.value.includes(prescriptionId);
    }

    function patchPrescription(
        prescriptionId: number,
        payload: RequestPayload,
        callbacks: { onSuccess?: () => void } = {},
    ): void {
        if (isPrescriptionUpdateInFlight(prescriptionId)) {
            return;
        }

        router.patch(
            route('patient.prescriptions.update', prescriptionId),
            payload,
            {
                preserveScroll: true,
                onStart: () => {
                    prescriptionIdsAwaitingResponse.value = [
                        ...prescriptionIdsAwaitingResponse.value,
                        prescriptionId,
                    ];
                },
                onSuccess: callbacks.onSuccess,
                onFinish: () => {
                    prescriptionIdsAwaitingResponse.value =
                        prescriptionIdsAwaitingResponse.value.filter(
                            (id) => id !== prescriptionId,
                        );
                },
            },
        );
    }

    function updatePrescriptionPickupStatus(
        prescriptionId: number,
        pickupStatus: MedicationPrescriptionPickupStatusValue,
        onPickedUp: (() => void) | null = null,
    ): void {
        const pickedUpHandler = onPickedUp ?? options.onPickedUp;

        patchPrescription(
            prescriptionId,
            { pickup_status: pickupStatus },
            {
                onSuccess: () => {
                    if (pickupStatus === 'picked_up') {
                        pickedUpHandler?.();
                    }
                },
            },
        );
    }

    return {
        isPrescriptionUpdateInFlight,
        updatePrescriptionPickupStatus,
    };
}
