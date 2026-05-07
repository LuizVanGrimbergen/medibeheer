<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import type { PageProps } from '@/lib/types';

const props = defineProps<{
    userName: string;
}>();

const page = usePage<PageProps>();

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

const { t } = useI18n();
</script>

<template>
    <nav class="border-b border-border bg-surface">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <Link
                :href="homeHref"
                class="text-base font-semibold text-primary transition hover:opacity-80"
            >
                
            </Link>

            <div class="flex items-center gap-5">
                <span class="hidden text-sm font-medium text-text-muted sm:inline">
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
