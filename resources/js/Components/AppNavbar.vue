<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';

const props = defineProps<{
    userName: string;
}>();

const { t } = useI18n();
</script>

<template>
    <nav class="border-b border-border bg-surface">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <Link
                :href="route('dashboard')"
                class="text-base font-semibold text-primary transition hover:opacity-80"
            >
                Medibeheer
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
