<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Badge } from '@/Components/ui/badge';
import { Separator } from '@/Components/ui/separator';

const props = withDefaults(
    defineProps<{
        titleKey: string;
        subtitleKey: string;
        showBranding?: boolean;
        appendAppName?: boolean;
    }>(),
    {
        showBranding: false,
        appendAppName: false,
    },
);

const { t } = useI18n();
</script>

<template>
    <header class="mb-8 text-center">
        <div v-if="props.showBranding" class="mx-auto mb-4 flex w-fit items-center gap-3">
            <Badge class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary p-0 text-2xl text-white shadow-sm hover:bg-primary">
                <span aria-hidden="true">🔗</span>
            </Badge>
            <p class="text-3xl font-semibold text-text">{{ t('app.name') }}</p>
        </div>
        <h1 class="mx-auto max-w-sm text-2xl leading-tight font-semibold text-text sm:max-w-none sm:text-3xl">
            {{ t(props.titleKey) }}
            <span v-if="props.appendAppName" class="text-primary">
                {{ t('app.name') }}
            </span>
        </h1>
        <p class="mt-2 text-lg text-text-muted">{{ t(props.subtitleKey) }}</p>
        <Separator class="mx-auto mt-4 w-24 bg-border" />
    </header>
</template>
