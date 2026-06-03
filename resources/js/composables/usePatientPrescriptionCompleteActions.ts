import type { RequestPayload } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { MedicationPrescriptionPickupStatusValue } from '@/lib/types';

export function usePatientPrescriptionCompleteActions() {
    const prescriptionIdsAwaitingResponse = ref<number[]>([]);

    function isPrescriptionUpdateInFlight(prescriptionId: number): boolean {
        return prescriptionIdsAwaitingResponse.value.includes(prescriptionId);
    }

    function patchPrescription(
        prescriptionId: number,
        payload: RequestPayload,
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
    ): void {
        patchPrescription(prescriptionId, { pickup_status: pickupStatus });
    }

    return {
        isPrescriptionUpdateInFlight,
        updatePrescriptionPickupStatus,
    };
}
