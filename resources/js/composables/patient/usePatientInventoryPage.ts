import { computed, type ComputedRef } from 'vue';
import { isDeferredInertiaPropLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import { compareMedicationInventoryListItems } from '@/lib/patient/inventory/medicationInventoryListSortRank';
import type { PatientInventoryScreenProps } from '@/lib/patient/inventory/patientInventoryScreenProps';
import type { MedicationRegisterItem } from '@/lib/types';

export function usePatientInventoryPage(props: PatientInventoryScreenProps): {
    isInventoryLoading: ComputedRef<boolean>;
    sortedInventoryMedications: ComputedRef<MedicationRegisterItem[]>;
    showInventoryEmpty: ComputedRef<boolean>;
    showVacationButton: ComputedRef<boolean>;
} {
    const isInventoryLoading = computed(() =>
        isDeferredInertiaPropLoading(props.medications),
    );

    const sortedInventoryMedications = computed((): MedicationRegisterItem[] => {
        if (props.medications === undefined) {
            return [];
        }

        const items = [...props.medications.data];

        items.sort(compareMedicationInventoryListItems);

        return items;
    });

    const showInventoryEmpty = computed(
        () =>
            !isInventoryLoading.value &&
            props.medications !== undefined &&
            props.medications.meta.total === 0,
    );

    const showVacationButton = computed(
        () => (props.medications?.meta.total ?? 0) > 0,
    );

    return {
        isInventoryLoading,
        sortedInventoryMedications,
        showInventoryEmpty,
        showVacationButton,
    };
}
