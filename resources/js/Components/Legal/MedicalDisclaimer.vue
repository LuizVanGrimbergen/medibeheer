<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const props = withDefaults(
    defineProps<{
        messageKey?: string;
    }>(),
    {
        messageKey: 'legal.disclaimer.short',
    },
);

const { t } = useI18n();
</script>

<template>
    <p
        class="text-text-muted border-border/70 bg-surface rounded-xl border px-4 py-3 text-sm leading-relaxed"
        role="note"
    >
        {{ t(props.messageKey) }}
    </p>
</template>
