<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/Components/AppLogo.vue';
import AppSettingsNavButton from '@/Components/AppSettingsNavButton.vue';
import DoctorNavbarLinks from '@/Components/Doctor/DoctorNavbarLinks.vue';
import type { PageProps } from '@/lib/types';

const props = defineProps<{
    userName: string;
}>();

const page = usePage<PageProps>();

const isDoctor = computed(() => page.props.auth.user?.role === 'doctor');

const homeHref = computed(() => {
    const role = page.props.auth.user?.role;

    if (role === 'patient') {
        return route('patient.dashboard');
    }

    if (role === 'family_member') {
        return route('family.overview');
    }

    if (role === 'doctor') {
        return route('doctor.dashboard');
    }

    return route('settings.edit');
});
</script>

<template>
    <nav
        v-if="isDoctor"
        class="border-border bg-surface sticky top-0 z-100 border-b"
    >
        <div
            class="mx-auto grid max-w-7xl grid-cols-[1fr_auto_1fr] items-center gap-4 px-4 py-4 sm:px-6 lg:px-8"
        >
            <Link
                :href="homeHref"
                class="justify-self-start transition hover:opacity-80"
            >
                <AppLogo />
            </Link>

            <DoctorNavbarLinks class="justify-self-center" />

            <div class="flex items-center justify-end gap-5 justify-self-end">
                <span
                    class="text-text-muted hidden text-sm font-medium sm:inline"
                >
                    {{ props.userName }}
                </span>

                <AppSettingsNavButton />
            </div>
        </div>
    </nav>

    <template v-else>
        <nav
            class="border-border bg-surface sticky top-0 z-100 hidden border-b md:block"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8"
            >
                <Link :href="homeHref" class="transition hover:opacity-80">
                    <AppLogo />
                </Link>

                <div class="flex items-center gap-5">
                    <span class="text-text-muted text-sm font-medium">
                        {{ props.userName }}
                    </span>

                    <AppSettingsNavButton />
                </div>
            </div>
        </nav>
    </template>
</template>
