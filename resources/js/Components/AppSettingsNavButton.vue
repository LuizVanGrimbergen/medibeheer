<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        tone?: 'default' | 'primary';
    }>(),
    {
        tone: 'default',
    },
);

const { t } = useI18n();
</script>

<template>
    <Button
        as-child
        variant="ghost"
        size="icon"
        :class="
            cn(
                '[&_svg]:size-[18px] [&_svg]:stroke-[1.75]',
                props.tone === 'primary' &&
                    'text-primary hover:bg-primary/10 hover:text-primary hover:opacity-100',
            )
        "
        :aria-label="t('app.navigation.settings')"
    >
        <Link :href="route('settings.edit')">
            <Settings class="shrink-0" aria-hidden="true" />
        </Link>
    </Button>
</template>
