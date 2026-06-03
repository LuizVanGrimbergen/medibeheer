<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutDashboard, UsersRound } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import {
    doctorNavItems,
    useDoctorRoleNavigation,
} from '@/composables/doctor/useDoctorRoleNavigation';
import { cn } from '@/lib/utils';

const { t } = useI18n();
const { isActive } = useDoctorRoleNavigation();

const navIcons = {
    'doctor.dashboard': LayoutDashboard,
    'doctor.patients': UsersRound,
} as const;

function linkClass(
    routeName: (typeof doctorNavItems)[number]['routeName'],
): string {
    return cn(
        'inline-flex w-[7.5rem] items-center justify-center gap-2 rounded-lg px-3 py-1.5 text-sm font-semibold transition-colors',
        isActive(routeName)
            ? 'bg-primary/12 text-primary'
            : 'text-text-muted hover:bg-surface-hover hover:text-text-heading',
    );
}
</script>

<template>
    <nav
        class="items-center gap-1 md:flex"
        :aria-label="t('doctor.navigation.ariaLabel')"
    >
        <Link
            v-for="item in doctorNavItems"
            :key="item.routeName"
            :href="route(item.routeName)"
            :prefetch="
                isActive(item.routeName) ? false : (['mount', 'hover'] as const)
            "
            :class="linkClass(item.routeName)"
            :aria-current="isActive(item.routeName) ? 'page' : undefined"
        >
            <component
                :is="navIcons[item.routeName]"
                class="size-4 shrink-0 stroke-[1.75]"
                aria-hidden="true"
            />
            {{ t(item.labelKey) }}
        </Link>
    </nav>
</template>
