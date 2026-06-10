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
import type { ComponentPublicInstance } from 'vue';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import MobileShellSettingsLink from '@/Components/MobileShellSettingsLink.vue';
import type { FooterNavLinkRefs } from '@/composables/motion/useGsapFooterNavIndicator';
import { useGsapFooterNavIndicator } from '@/composables/motion/useGsapFooterNavIndicator';
import { usePatientNavigationAlerts } from '@/composables/patient/usePatientNavigationAlerts';
import {
    isMobileShellFooterHidden,
    useMobileShellMainScrollReset,
} from '@/composables/patient/useMobileShellDialogChrome';
import { useTailwindBreakpoints } from '@/composables/ui/useTailwindBreakpoints';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { resolveGsapTargetElement } from '@/lib/motion/resolveGsapTargetElement';
import type { PatientFooterNavRouteName } from '@/lib/patient/navigation/patientFooterNavClasses';
import {
    patientFooterNavAlertAccentClass,
    patientFooterNavAlertTone,
} from '@/lib/patient/navigation/patientFooterNavClasses';
import {
    mobileShellFooterNavClass,
    mobileShellScrollContentClass,
} from '@/lib/shell/mobileShellLayout';
import type { PageProps } from '@/lib/types';
import { cn } from '@/lib/utils';

const { t } = useI18n();
const page = usePage<PageProps>();
const { smAndUp } = useTailwindBreakpoints();

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

const pendingFooterNavRoute = ref<PatientFooterNavRouteName | undefined>();

const isMobileFooterNav = computed(() => !smAndUp.value);

const footerNavIndicatorRoute = computed(
    (): PatientFooterNavRouteName | undefined =>
        pendingFooterNavRoute.value ?? activePatientNavRoute.value,
);

const footerNavActiveRoute = computed(
    (): PatientFooterNavRouteName | undefined =>
        isMobileFooterNav.value
            ? footerNavIndicatorRoute.value
            : activePatientNavRoute.value,
);

watch(isMobileFooterNav, (isMobile) => {
    if (!isMobile) {
        pendingFooterNavRoute.value = undefined;
    }
});

watch(activePatientNavRoute, (route) => {
    if (route === pendingFooterNavRoute.value) {
        pendingFooterNavRoute.value = undefined;
    }
});

function onFooterNavNavigate(routeName: PatientFooterNavRouteName): void {
    if (
        !isMobileFooterNav.value ||
        routeName === activePatientNavRoute.value
    ) {
        return;
    }

    pendingFooterNavRoute.value = routeName;
}

const patientNavigation = usePatientNavigationAlerts();

const mainScrollRef = ref<HTMLElement | null>(null);
const footerNavRef = ref<HTMLElement | null>(null);
const footerNavIndicatorRef = ref<HTMLElement | null>(null);
const footerNavLinkRefs: FooterNavLinkRefs = {};

useMobileShellMainScrollReset(mainScrollRef);

useGsapFooterNavIndicator(
    footerNavRef,
    footerNavIndicatorRef,
    footerNavIndicatorRoute,
    footerNavLinkRefs,
    isMobileFooterNav,
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

    if (alertTone === null || footerNavActiveRoute.value === item.routeName) {
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

    if (footerNavActiveRoute.value !== routeName) {
        return `${base} text-text-muted`;
    }

    if (isMobileFooterNav.value) {
        return `${base} text-primary`;
    }

    return `${base} bg-primary/12 text-primary`;
}

function footerNavAlertAccentClass(
    routeName: PatientNavItem['routeName'],
): string | null {
    if (footerNavActiveRoute.value === routeName) {
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
        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
            <div
                ref="mainScrollRef"
                :class="
                    cn(
                        'h-0 min-h-0 min-w-0 flex-1 overflow-x-hidden overscroll-y-contain',
                        isMobileShellFooterHidden
                            ? 'overflow-hidden'
                            : 'overflow-y-auto',
                    )
                "
            >
                <div
                    :class="
                        cn(
                            mobileShellScrollContentClass,
                            isMobileShellFooterHidden &&
                                'flex h-full min-h-0 flex-col pb-0',
                        )
                    "
                >
                    <MobileShellSettingsLink />
                    <slot />
                </div>
            </div>

            <nav
                v-show="!isMobileShellFooterHidden"
                class="border-border bg-surface z-40 shrink-0 border-t"
                :aria-label="t('patient.navigation.mobileFooterAriaLabel')"
                :aria-hidden="isMobileShellFooterHidden"
            >
                <div ref="footerNavRef" :class="mobileShellFooterNavClass">
                    <div
                        v-show="isMobileFooterNav"
                        ref="footerNavIndicatorRef"
                        class="bg-primary/12 pointer-events-none absolute left-0 z-0 rounded-xl opacity-0 will-change-transform md:hidden"
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
                        :aria-current="
                            activePatientNavRoute === item.routeName
                                ? 'page'
                                : undefined
                        "
                        :aria-label="footerNavAriaLabel(item)"
                        @click="onFooterNavNavigate(item.routeName)"
                    >
                        <component
                            :is="item.icon"
                            :class="footerNavIconClass(item.routeName)"
                            aria-hidden="true"
                        />
                        <span
                            :class="footerNavLabelClassForItem(item.routeName)"
                        >
                            {{ t(item.labelKey) }}
                        </span>
                    </Link>
                </div>
            </nav>
        </div>
    </AuthenticatedLayout>
</template>
