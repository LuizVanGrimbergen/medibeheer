import { usePage } from '@inertiajs/vue3';
import type { ComputedRef } from 'vue';
import { computed } from 'vue';
import type { PatientNavigationSharedProps } from '@/lib/patient/navigation/patientFooterNavClasses';
import { resolvePatientNavigationAlerts } from '@/lib/patient/navigation/resolvePatientNavigationAlerts';
import type { PageProps } from '@/lib/types';

export function usePatientNavigationAlerts(): ComputedRef<PatientNavigationSharedProps> {
    const page = usePage<PageProps>();

    return computed(() => resolvePatientNavigationAlerts(page.props));
}
