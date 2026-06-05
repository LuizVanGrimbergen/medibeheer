import { router } from '@inertiajs/vue3';

export function useFamilyActivePatientSwitch(): {
    switchToPatient: (switchUrl: string, isActive: boolean) => void;
} {
    function switchToPatient(switchUrl: string, isActive: boolean): void {
        if (isActive) {
            return;
        }

        router.post(switchUrl, {}, { preserveScroll: true });
    }

    return { switchToPatient };
}
