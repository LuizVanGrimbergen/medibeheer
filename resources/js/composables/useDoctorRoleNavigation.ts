import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export type DoctorNavRouteName = 'doctor.dashboard' | 'doctor.patients';

export type DoctorNavItem = {
    routeName: DoctorNavRouteName;
    labelKey: 'doctor.navigation.home' | 'doctor.navigation.patients';
};

export const doctorNavItems: readonly DoctorNavItem[] = [
    {
        routeName: 'doctor.dashboard',
        labelKey: 'doctor.navigation.home',
    },
    {
        routeName: 'doctor.patients',
        labelKey: 'doctor.navigation.patients',
    },
];

function pathOnly(urlOrPath: string): string {
    const raw = urlOrPath.startsWith('http')
        ? new URL(urlOrPath).pathname
        : (urlOrPath.split('?')[0] ?? '');

    if (raw.length > 1 && raw.endsWith('/')) {
        return raw.slice(0, -1);
    }

    return raw;
}

export function useDoctorRoleNavigation() {
    const page = usePage();

    const activeRoute = computed((): DoctorNavRouteName | undefined => {
        const pathname = pathOnly(page.url);

        return doctorNavItems.find(
            (item) => pathname === pathOnly(route(item.routeName) as string),
        )?.routeName;
    });

    function isActive(routeName: DoctorNavRouteName): boolean {
        return activeRoute.value === routeName;
    }

    return {
        activeRoute,
        isActive,
    };
}
