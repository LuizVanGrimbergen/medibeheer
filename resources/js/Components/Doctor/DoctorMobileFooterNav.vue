<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutDashboard, UsersRound } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    doctorNavItems,
    useDoctorRoleNavigation,
} from '@/composables/useDoctorRoleNavigation';
import { useTailwindBreakpoints } from '@/composables/useTailwindBreakpoints';

const { t } = useI18n();
const { isActive } = useDoctorRoleNavigation();
const { smAndUp } = useTailwindBreakpoints();

const navIcons = {
    'doctor.dashboard': LayoutDashboard,
    'doctor.patients': UsersRound,
} as const;

function footerNavClass(routeName: (typeof doctorNavItems)[number]['routeName']): string {
    const density = smAndUp.value
        ? 'gap-1.5 py-2.5 px-2'
        : 'gap-1 py-2 px-1';
    const base = `flex min-w-0 flex-1 touch-manipulation flex-col items-center justify-center rounded-xl transition-colors ${density}`;

    if (isActive(routeName)) {
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
        : 'max-w-full truncate text-center text-2xs font-semibold leading-tight tracking-tight',
);
</script>

<template>
    <nav
        class="z-40 shrink-0 border-t border-border bg-surface md:hidden"
        :aria-label="t('doctor.navigation.mobileFooterAriaLabel')"
    >
        <div
            class="mx-auto flex max-w-7xl items-stretch justify-around px-4 pt-2 pb-[max(0.5rem,env(safe-area-inset-bottom,0px))] sm:px-6"
        >
            <Link
                v-for="item in doctorNavItems"
                :key="item.routeName"
                :href="route(item.routeName)"
                :prefetch="
                    isActive(item.routeName)
                        ? false
                        : (['mount', 'hover'] as const)
                "
                :class="footerNavClass(item.routeName)"
                :aria-current="isActive(item.routeName) ? 'page' : undefined"
            >
                <component
                    :is="navIcons[item.routeName]"
                    :class="footerIconClass"
                    aria-hidden="true"
                />
                <span :class="footerLabelClass">
                    {{ t(item.labelKey) }}
                </span>
            </Link>
        </div>
    </nav>
</template>
