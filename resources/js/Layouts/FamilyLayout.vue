<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import { Bell, LayoutGrid } from 'lucide-vue-next';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTailwindBreakpoints } from '@/composables/useTailwindBreakpoints';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { PageProps } from '@/lib/types';

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

type FamilyNavItem = {
    routeName: 'family.overview' | 'family.updates';
    labelKey: 'family.navigation.overview' | 'family.navigation.updates';
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

const familyNavItems: readonly FamilyNavItem[] = [
    {
        routeName: 'family.overview',
        labelKey: 'family.navigation.overview',
        icon: LayoutGrid,
    },
    {
        routeName: 'family.updates',
        labelKey: 'family.navigation.updates',
        icon: Bell,
    },
];

const activeFamilyNavRoute = computed((): FamilyNavItem['routeName'] | undefined => {
    const pathname = pathOnly(page.url);

    return familyNavItems.find(
        (item) => pathname === pathOnly(route(item.routeName) as string),
    )?.routeName;
});

function footerNavClass(routeName: FamilyNavItem['routeName']): string {
    const density = smAndUp.value
        ? 'gap-1.5 py-2.5 px-2'
        : 'gap-1 py-2 px-1';
    const base = `flex min-w-0 flex-1 flex-col items-center justify-center rounded-xl transition-colors ${density}`;

    if (activeFamilyNavRoute.value === routeName) {
        return `${base} bg-primary/12 text-primary`;
    }

    return `${base} text-text-muted`;
}

const footerIconClass = computed(() =>
    smAndUp.value
        ? 'size-6 shrink-0 stroke-[1.75]'
        : 'size-[22px] shrink-0 stroke-[1.75]',
);

const footerLabelClass = computed(() =>
    smAndUp.value
        ? 'max-w-full truncate text-center text-xs font-semibold leading-tight tracking-tight'
        : 'max-w-full truncate text-center text-[11px] font-semibold leading-tight tracking-tight',
);
</script>

<template>
    <AuthenticatedLayout>
        <div
            class="relative mx-auto flex w-full max-w-7xl flex-1 flex-col py-6 pb-[calc(5.5rem+env(safe-area-inset-bottom,0))]"
            :class="shellPaddingX"
        >
            <div class="min-h-[calc(100dvh-5rem)] min-w-0 flex-1">
                <slot />
            </div>

            <nav
                class="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-surface"
                :aria-label="t('family.navigation.mobileFooterAriaLabel')"
            >
                <div
                    class="mx-auto flex max-w-7xl items-stretch justify-around pt-2 pb-[max(0.5rem,env(safe-area-inset-bottom,0px))] shadow-[0_-6px_24px_rgba(31,51,74,0.08)]"
                    :class="footerPaddingX"
                >
                    <Link
                        v-for="item in familyNavItems"
                        :key="item.routeName"
                        :href="route(item.routeName)"
                        :class="footerNavClass(item.routeName)"
                    >
                        <component
                            :is="item.icon"
                            :class="footerIconClass"
                            aria-hidden="true"
                        />
                        <span :class="footerLabelClass">
                            {{ t(item.labelKey) }}
                        </span>
                    </Link>
                </div>
            </nav>
        </div>
    </AuthenticatedLayout>
</template>
