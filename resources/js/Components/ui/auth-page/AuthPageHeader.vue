<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Separator } from '@/Components/ui/separator';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        titleKey: string;
        titleAccentKey?: string;
        subtitleKey?: string;
        appendAppName?: boolean;
        showSubtitle?: boolean;
    }>(),
    {
        appendAppName: false,
        showSubtitle: true,
    },
);

const { t } = useI18n();

const isHeroHeader = computed(() => !props.showSubtitle);
</script>

<template>
    <header
        :class="cn('text-center', isHeroHeader ? 'mb-6' : 'mb-8')"
    >
        <h1
            :class="
                cn(
                    'mx-auto leading-tight',
                    isHeroHeader
                        ? 'max-w-md text-3xl font-bold tracking-tight text-text-heading sm:text-4xl'
                        : 'max-w-sm text-2xl font-semibold text-text sm:max-w-none sm:text-3xl',
                )
            "
        >
            <template v-if="props.titleAccentKey !== undefined">
                <span class="text-primary">{{ t(props.titleAccentKey) }}</span>
                {{ ' ' }}
                <span>{{ t(props.titleKey) }}</span>
            </template>
            <template v-else>
                {{ t(props.titleKey) }}
                <span
                    v-if="props.appendAppName"
                    class="text-primary"
                >
                    {{ ' ' }}{{ t('app.name') }}
                </span>
            </template>
        </h1>

        <template v-if="props.showSubtitle && props.subtitleKey !== undefined">
            <p class="mt-2 text-lg text-text-muted">
                {{ t(props.subtitleKey) }}
            </p>
            <Separator class="mx-auto mt-4 w-24 bg-border" />
        </template>
    </header>
</template>
