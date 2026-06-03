<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLogo from '@/Components/AppLogo.vue';
import DoctorNavbarLinks from '@/Components/Doctor/DoctorNavbarLinks.vue';
import { Button } from '@/Components/ui/button';
import type { PageProps } from '@/lib/types';

const props = defineProps<{
    userName: string;
}>();

const page = usePage<PageProps>();
const { t } = useI18n();

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
    <nav class="border-border bg-surface sticky top-0 z-100 border-b">
        <div
            v-if="isDoctor"
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

                <Button
                    as-child
                    variant="ghost"
                    size="icon"
                    :aria-label="t('app.navigation.settings')"
                >
                    <Link :href="route('settings.edit')">
                        <Settings :size="18" />
                    </Link>
                </Button>
            </div>
        </div>

        <div
            v-else
            class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8"
        >
            <Link :href="homeHref" class="transition hover:opacity-80">
                <AppLogo />
            </Link>

            <div class="flex items-center gap-5">
                <span
                    class="text-text-muted hidden text-sm font-medium sm:inline"
                >
                    {{ props.userName }}
                </span>

                <Button
                    as-child
                    variant="ghost"
                    size="icon"
                    :aria-label="t('app.navigation.settings')"
                >
                    <Link :href="route('settings.edit')">
                        <Settings :size="18" />
                    </Link>
                </Button>
            </div>
        </div>
    </nav>
</template>
