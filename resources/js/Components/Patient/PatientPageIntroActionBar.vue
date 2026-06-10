<script setup lang="ts">
import {
    mobileShellPageActionsBarClass,
    mobileShellPageActionsGridClass,
} from '@/lib/shell/mobileShellTypography';

withDefaults(
    defineProps<{
        show?: boolean;
    }>(),
    {
        show: true,
    },
);
</script>

<template>
    <div v-if="show" :class="mobileShellPageActionsBarClass">
        <div :class="mobileShellPageActionsGridClass">
            <slot />
        </div>
    </div>
</template>
