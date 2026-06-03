<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import {
    Calendar,
    FileText,
    House,
    Package,
    Pill,
    UserRound,
} from 'lucide-vue-next';
import type { ComputedRef } from 'vue';
import { computed, ref, type ComponentPublicInstance } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientFlashActionSuccessScreen from '@/Components/Patient/PatientFlashActionSuccessScreen.vue';
import {
    type FooterNavLinkRefs,
    useGsapFooterNavIndicator,
} from '@/composables/motion/useGsapFooterNavIndicator';
import { usePatientNavigationAlerts } from '@/composables/patient/usePatientNavigationAlerts';
import { useTailwindBreakpoints } from '@/composables/ui/useTailwindBreakpoints';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    patientFooterNavAlertAccentClass,
    patientFooterNavAlertTone,
    type PatientFooterNavRouteName,
} from '@/lib/patient/navigation/patientFooterNavClasses';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import type { PageProps } from '@/lib/types';
import { cn } from '@/lib/utils';

const { t } = useI18n();
const page = usePage<PageProps>();
const { smAndUp, lgAndUp } = useTailwindBreakpoints();

function horizontalPaddingX(
    atLg: string,
    atSm: string,
    base: string,
): ComputedRef<string> {
    return computed(() => {
        if (lgAndUp.value) {
            return atLg;
        }

        if (smAndUp.value) {
            return atSm;
        }

        return base;
    });
}

const shellPaddingX = horizontalPaddingX('px-8', 'px-6', 'px-4');
const footerPaddingX = horizontalPaddingX('px-8', 'px-4', 'px-1');

type PatientNavItem = {
    routeName: PatientFooterNavRouteName;
    labelKey:
        | 'patient.navigation.home'
        | 'patient.navigation.medications'
        | 'patient.navigation.prescriptions'
        | 'patient.navigation.inventory'
        | 'patient.navigation.appointments'
        | 'patient.navigation.family';
    icon: LucideIcon;
};

function pathOnly(urlOrPath: string): string {
    const raw = urlOrPath.startsWith('http')
        ? new URL(urlOrPath).pathname
        : (urlOrPath.split('?')[0] ?? '');

    if (raw.length > 1 && raw.endsWith('/')) {
        return raw.slice(0, -1);
    }

    return raw;
}

const patientNavItems: readonly PatientNavItem[] = [
    {
        routeName: 'patient.dashboard',
        labelKey: 'patient.navigation.home',
        icon: House,
    },
    {
        routeName: 'patient.medications',
        labelKey: 'patient.navigation.medications',
        icon: Pill,
    },
    {
        routeName: 'patient.prescriptions',
        labelKey: 'patient.navigation.prescriptions',
        icon: FileText,
    },
    {
        routeName: 'patient.inventory',
        labelKey: 'patient.navigation.inventory',
        icon: Package,
    },
    {
        routeName: 'patient.appointments',
        labelKey: 'patient.navigation.appointments',
        icon: Calendar,
    },
    {
        routeName: 'patient.family',
        labelKey: 'patient.navigation.family',
        icon: UserRound,
    },
];

const activePatientNavRoute = computed(
    (): PatientNavItem['routeName'] | undefined => {
        const pathname = pathOnly(page.url);

        return patientNavItems.find(
            (item) => pathname === pathOnly(route(item.routeName) as string),
        )?.routeName;
    },
);

const patientNavigation = usePatientNavigationAlerts();

const footerNavRef = ref<HTMLElement | null>(null);
const footerNavIndicatorRef = ref<HTMLElement | null>(null);
const footerNavLinkRefs: FooterNavLinkRefs = {};

useGsapFooterNavIndicator(
    footerNavRef,
    footerNavIndicatorRef,
    activePatientNavRoute,
    footerNavLinkRefs,
);

function registerFooterNavLinkRef(
    routeName: PatientFooterNavRouteName,
    target: Element | ComponentPublicInstance | null,
): void {
    const element = resolveGsapTargetElement(
        target as HTMLElement | ComponentPublicInstance | null,
    );

    if (element === null) {
        delete footerNavLinkRefs[routeName];

        return;
    }

    if (footerNavLinkRefs[routeName] === element) {
        return;
    }

    footerNavLinkRefs[routeName] = element;
}

function footerNavAriaLabel(item: PatientNavItem): string | undefined {
    const alertTone = patientFooterNavAlertTone(
        item.routeName,
        patientNavigation.value,
    );

    if (alertTone === null || activePatientNavRoute.value === item.routeName) {
        return undefined;
    }

    if (alertTone === 'critical') {
        return t('patient.navigation.footerAlertCritical', {
            label: t(item.labelKey),
        });
    }

    return t('patient.navigation.footerAlertWarning', {
        label: t(item.labelKey),
    });
}

function footerNavLinkClass(routeName: PatientNavItem['routeName']): string {
    const density = smAndUp.value ? 'gap-1.5 py-2.5 px-2' : 'gap-1 py-2 px-1';
    const base = `relative z-10 flex min-w-0 flex-1 flex-col items-center justify-center rounded-xl ${density}`;

    if (activePatientNavRoute.value === routeName) {
        return `${base} text-primary`;
    }

    return `${base} text-text-muted`;
}

function footerNavAlertAccentClass(
    routeName: PatientNavItem['routeName'],
): string | null {
    if (activePatientNavRoute.value === routeName) {
        return null;
    }

    const alertTone = patientFooterNavAlertTone(
        routeName,
        patientNavigation.value,
    );

    if (alertTone === null) {
        return null;
    }

    return patientFooterNavAlertAccentClass(alertTone);
}

function footerNavIconClass(routeName: PatientNavItem['routeName']): string {
    return cn(footerIconClass.value, footerNavAlertAccentClass(routeName));
}

function footerNavLabelClassForItem(
    routeName: PatientNavItem['routeName'],
): string {
    return cn(footerLabelClass.value, footerNavAlertAccentClass(routeName));
}

const footerIconClass = computed(() =>
    smAndUp.value
        ? 'size-6 shrink-0 stroke-[1.75]'
        : 'size-[22px] shrink-0 stroke-[1.75]',
);

const footerLabelClass = computed(() =>
    smAndUp.value
        ? 'max-w-full truncate text-center text-xs font-semibold leading-tight tracking-tight'
        : 'max-w-full truncate text-center text-2xs font-semibold leading-tight tracking-tight',
);
</script>

<template>
    <AuthenticatedLayout>
        <PatientFlashActionSuccessScreen />

        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
            <div
                class="h-0 min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain"
            >
                <div
                    class="relative mx-auto w-full max-w-7xl pt-6 pb-4"
                    :class="shellPaddingX"
                >
                    <slot />
                </div>
            </div>

            <nav
                class="border-border bg-surface z-40 shrink-0 border-t"
                :aria-label="t('patient.navigation.mobileFooterAriaLabel')"
            >
                <div
                    ref="footerNavRef"
                    class="relative mx-auto flex max-w-7xl items-stretch justify-around pt-2 pb-[max(0.5rem,env(safe-area-inset-bottom,0px))]"
                    :class="footerPaddingX"
                >
                    <div
                        ref="footerNavIndicatorRef"
                        class="bg-primary/12 pointer-events-none absolute z-0 rounded-xl opacity-0"
                        aria-hidden="true"
                    />

                    <Link
                        v-for="item in patientNavItems"
                        :key="item.routeName"
                        :ref="
                            (target) =>
                                registerFooterNavLinkRef(item.routeName, target)
                        "
                        :href="route(item.routeName)"
                        :prefetch="
                            activePatientNavRoute === item.routeName
                                ? false
                                : (['mount', 'hover'] as const)
                        "
                        :class="footerNavLinkClass(item.routeName)"
                        :aria-label="footerNavAriaLabel(item)"
                    >
                        <component
                            :is="item.icon"
                            :class="footerNavIconClass(item.routeName)"
                            aria-hidden="true"
                        />
                        <span :class="footerNavLabelClassForItem(item.routeName)">
                            {{ t(item.labelKey) }}
                        </span>
                    </Link>
                </div>
            </nav>
        </div>
    </AuthenticatedLayout>
</template>
