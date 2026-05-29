<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Card, CardContent } from '@/Components/ui/card';
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
    <div class="mb-6 grid grid-cols-3 gap-3">
        <Link
            v-for="role in roles"
            :key="role.key"
            :href="getHref(role.key)"
            preserve-state
            class="group block rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2"
        >
            <Card
                :class="
                    cn(
                        'h-20 border px-3 transition-colors duration-200 sm:h-24',
                        role.ringClass,
                        roleActiveSurfaceClass[role.key],
                        selectedRole === role.key
                            ? roleSelectedSurfaceClass[role.key]
                            : '',
                    )
                "
            >
                <CardContent
                    class="flex h-full items-center justify-center p-0 text-center text-sm font-semibold sm:text-base [&_svg]:shrink-0"
                >
                    <span class="flex flex-col items-center gap-1.5">
                        <component
                            :is="role.icon"
                            class="text-inherit"
                            :size="18"
                            stroke-width="2"
                        />
                        <span class="font-bold">{{ role.label }}</span>
                    </span>
                </CardContent>
            </Card>
        </Link>
    </div>
</template>
