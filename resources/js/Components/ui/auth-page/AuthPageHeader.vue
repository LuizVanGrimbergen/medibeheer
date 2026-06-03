<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLogo from '@/Components/AppLogo.vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        titleKey: string;
        titleAccentKey?: string;
        subtitleKey?: string;
        subtitleTone?: 'muted' | 'body';
        appendAppName?: boolean;
        showSubtitle?: boolean;
    }>(),
    {
        appendAppName: false,
        showSubtitle: true,
        subtitleTone: 'muted',
    },
);

const { t } = useI18n();

const isHeroHeader = computed(
    () =>
        props.appendAppName
        || props.titleAccentKey !== undefined
        || !props.showSubtitle,
);
</script>

<template>
    <header
        :class="cn('text-center', isHeroHeader ? 'mb-6' : 'mb-8')"
    >
        <div class="mb-6 flex justify-center">
            <AppLogo
                :class="isHeroHeader ? 'h-20 w-auto sm:h-24' : 'h-14 w-auto sm:h-16'"
            />
        </div>

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
            <p
                :class="
                    cn(
                        'mx-auto max-w-md',
                        props.subtitleTone === 'body'
                            ? 'mt-3 text-base font-medium leading-relaxed text-text sm:text-lg'
                            : 'mt-2 text-lg text-text-muted',
                    )
                "
            >
                {{ t(props.subtitleKey) }}
            </p>
        </template>
    </header>
</template>
