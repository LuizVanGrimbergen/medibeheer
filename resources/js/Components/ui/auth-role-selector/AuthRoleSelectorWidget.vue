<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { RoleKey, RoleOption } from '@/lib/types';
import { cn } from '@/lib/utils';

const roleActiveSurfaceClass: Record<RoleKey, string> = {
    patient:
        'group-hover:border-role-patient-active group-hover:bg-role-patient-active group-hover:text-white',
    doctor:
        'group-hover:border-role-doctor-active group-hover:bg-role-doctor-active group-hover:text-white',
    family_member:
        'group-hover:border-role-family-active group-hover:bg-role-family-active group-hover:text-white',
};

const roleSelectedSurfaceClass: Record<RoleKey, string> = {
    patient: 'border-role-patient-active! bg-role-patient-active! text-white!',
    doctor: 'border-role-doctor-active! bg-role-doctor-active! text-white!',
    family_member: 'border-role-family-active! bg-role-family-active! text-white!',
};

defineProps<{
    roles: readonly RoleOption[];
    selectedRole: RoleKey | null;
    getHref: (role: RoleKey) => string;
}>();
</script>

<template>
    <div class="mb-4 grid grid-cols-3 items-start gap-2 sm:mb-6 sm:gap-3 md:gap-4">
        <Link
            v-for="role in roles"
            :key="role.key"
            :href="getHref(role.key)"
            preserve-state
            :class="
                cn(
                    'group box-border flex aspect-square w-full min-w-0 shrink-0 flex-col items-center justify-center gap-2 overflow-hidden rounded-xl border p-3 text-center shadow-sm transition-colors duration-200 touch-manipulation focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2 sm:gap-2 sm:p-3 md:gap-2.5 md:rounded-xl',
                    role.ringClass,
                    roleActiveSurfaceClass[role.key],
                    selectedRole === role.key
                        ? roleSelectedSurfaceClass[role.key]
                        : '',
                )
            "
        >
            <component
                :is="role.icon"
                class="size-8 shrink-0 text-inherit sm:size-8 md:size-8"
                stroke-width="2"
            />
            <span class="max-w-full overflow-hidden text-ellipsis whitespace-nowrap px-0.5 text-xs font-bold leading-tight sm:text-xs md:text-sm">
                {{ role.label }}
            </span>
        </Link>
    </div>
</template>
