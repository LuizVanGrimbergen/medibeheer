<script setup lang="ts">
import {
    mobileShellPageCardDetailIconWrapperClass,
    mobileShellPageCardDetailLabelClass,
    mobileShellPageCardDetailValueClass,
} from '@/lib/shell/mobileShellTypography';

const props = withDefaults(
    defineProps<{
        label: string;
        /** Custom content in the value slot (times list, time element, …). */
        rawValue?: boolean;
    }>(),
    {
        rawValue: false,
    },
);
</script>

<template>
    <div class="flex gap-4 sm:gap-5">
        <div v-if="$slots.icon" :class="mobileShellPageCardDetailIconWrapperClass">
            <slot name="icon" />
        </div>
        <div class="min-w-0 flex-1 space-y-1.5">
            <p :class="mobileShellPageCardDetailLabelClass">
                {{ props.label }}
            </p>
            <slot v-if="props.rawValue" />
            <p v-else :class="mobileShellPageCardDetailValueClass">
                <slot />
            </p>
        </div>
    </div>
</template>
