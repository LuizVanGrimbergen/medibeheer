import { computed, type ComputedRef } from 'vue';
import { areAnyDeferredInertiaPropsLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import type { PatientFamilyScreenProps } from '@/lib/patient/family/patientFamilyScreenProps';

export function usePatientFamilyPage(props: PatientFamilyScreenProps): {
    isCareTeamLoading: ComputedRef<boolean>;
} {
    const isCareTeamLoading = computed(() =>
        areAnyDeferredInertiaPropsLoading(
            props.family_invitations,
            props.pending_medication_plans,
            props.accepted_medication_plans,
            props.doctor_invitations,
            props.linked_doctors,
            props.linked_family_members,
        ),
    );

    return {
        isCareTeamLoading,
    };
}
