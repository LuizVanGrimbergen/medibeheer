<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { useI18n } from 'vue-i18n';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        class?: HTMLAttributes['class'];
    }>(),
    {},
);

const { t } = useI18n();
</script>

<template>
    <img
        src="/favicon.svg"
        :alt="t('app.name')"
        :class="cn('h-8 w-auto', props.class)"
    />
</template>
