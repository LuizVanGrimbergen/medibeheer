<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import { CalendarDays, LayoutGrid, Link2, Pill, Smile } from 'lucide-vue-next';
import type { ComputedRef } from 'vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MobileShellSettingsLink from '@/Components/MobileShellSettingsLink.vue';
import { useTailwindBreakpoints } from '@/composables/ui/useTailwindBreakpoints';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { FamilyDashboardProps, PageProps } from '@/lib/types';

type PageWithFamily = PageProps & { family?: FamilyDashboardProps };

const { t } = useI18n();
const page = usePage<PageWithFamily>();
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

type FamilyNavItem = {
    routeName:
        | 'family.overview'
        | 'family.appointments'
        | 'family.medications'
        | 'family.link'
        | 'family.wellbeing';
    labelKey:
        | 'family.navigation.overview'
        | 'family.navigation.appointments'
        | 'family.navigation.medications'
        | 'family.navigation.link'
        | 'family.navigation.wellbeing';
    icon: LucideIcon;
    requiresLinkedPatient?: boolean;
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

const allFamilyNavItems: readonly FamilyNavItem[] = [
    {
        routeName: 'family.overview',
        labelKey: 'family.navigation.overview',
        icon: LayoutGrid,
    },
    {
        routeName: 'family.link',
        labelKey: 'family.navigation.link',
        icon: Link2,
    },
    {
        routeName: 'family.appointments',
        labelKey: 'family.navigation.appointments',
        icon: CalendarDays,
        requiresLinkedPatient: true,
    },
    {
        routeName: 'family.medications',
        labelKey: 'family.navigation.medications',
        icon: Pill,
        requiresLinkedPatient: true,
    },
    {
        routeName: 'family.wellbeing',
        labelKey: 'family.navigation.wellbeing',
        icon: Smile,
        requiresLinkedPatient: true,
    },
];

const visibleFamilyNavItems = computed((): readonly FamilyNavItem[] => {
    const f = page.props.family;

    if (f === undefined || f === null) {
        return allFamilyNavItems;
    }

    if (!f.has_linked_patient) {
        return allFamilyNavItems.filter((item) => !item.requiresLinkedPatient);
    }

    return allFamilyNavItems;
});

const activeFamilyNavRoute = computed(
    (): FamilyNavItem['routeName'] | undefined => {
        const pathname = pathOnly(page.url);

        if (
            pathname === pathOnly(route('family.link') as string) ||
            pathname.startsWith('/family/medication-plans')
        ) {
            return 'family.link';
        }

        return visibleFamilyNavItems.value.find(
            (item) => pathname === pathOnly(route(item.routeName) as string),
        )?.routeName;
    },
);

function footerNavClass(routeName: FamilyNavItem['routeName']): string {
    const density = smAndUp.value ? 'gap-1.5 py-2.5 px-2' : 'gap-1 py-2 px-1';
    const base = `flex min-w-0 flex-1 flex-col items-center justify-center rounded-xl transition-colors ${density}`;

    if (activeFamilyNavRoute.value === routeName) {
        return `${base} bg-primary/12 text-primary`;
    }

    return `${base} text-text-muted`;
}

const footerIconSizeClass = computed(() =>
    smAndUp.value
        ? 'size-6 shrink-0 stroke-[1.75]'
        : 'size-[22px] shrink-0 stroke-[1.75]',
);

const footerLabelClass = computed(() =>
    smAndUp.value
        ? 'max-w-full truncate text-center text-xs font-semibold leading-tight tracking-tight'
        : 'max-w-full truncate text-center text-2xs font-semibold leading-tight tracking-tight',
);

function navItemAriaLabel(item: FamilyNavItem): string | undefined {
    if (item.routeName !== 'family.wellbeing') {
        return undefined;
    }

    const family = page.props.family;

    if (!family?.has_linked_patient) {
        return undefined;
    }

    const mood = family.active_patient_today_mood ?? null;

    if (mood === null) {
        return t('family.navigation.wellbeingAriaNoCheckin');
    }

    return t('family.navigation.wellbeingAriaMood', {
        mood: t(`family.navigation.moodShort.${mood}`),
    });
}

function wellbeingNavUsesDetailedAria(item: FamilyNavItem): boolean {
    return (
        item.routeName === 'family.wellbeing' &&
        Boolean(page.props.family?.has_linked_patient)
    );
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
            <div
                class="h-0 min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain"
            >
                <div
                    class="relative mx-auto w-full max-w-7xl pt-4 pb-4 md:pt-6"
                    :class="shellPaddingX"
                >
                    <MobileShellSettingsLink />
                    <slot />
                </div>
            </div>

            <nav
                class="border-border bg-surface z-40 shrink-0 border-t"
                :aria-label="t('family.navigation.mobileFooterAriaLabel')"
            >
                <div
                    class="mx-auto flex max-w-7xl items-stretch justify-around pt-2 pb-[max(0.5rem,env(safe-area-inset-bottom,0px))]"
                    :class="footerPaddingX"
                >
                    <Link
                        v-for="item in visibleFamilyNavItems"
                        :key="item.routeName"
                        :href="route(item.routeName)"
                        :prefetch="
                            activeFamilyNavRoute === item.routeName
                                ? false
                                : (['mount', 'hover'] as const)
                        "
                        :class="footerNavClass(item.routeName)"
                        :aria-label="navItemAriaLabel(item)"
                    >
                        <component
                            :is="item.icon"
                            :class="footerIconSizeClass"
                            aria-hidden="true"
                        />
                        <span
                            :class="footerLabelClass"
                            :aria-hidden="
                                wellbeingNavUsesDetailedAria(item)
                                    ? true
                                    : undefined
                            "
                        >
                            {{ t(item.labelKey) }}
                        </span>
                    </Link>
                </div>
            </nav>
        </div>
    </AuthenticatedLayout>
</template>
