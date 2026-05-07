<script setup lang="ts">
import { reactiveOmit } from '@vueuse/core';
import type { HTMLAttributes } from 'vue';
import { Check } from 'lucide-vue-next';
import type { CheckboxRootEmits, CheckboxRootProps } from 'reka-ui';
import {
    CheckboxIndicator,
    CheckboxRoot,
    useForwardPropsEmits,
} from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps<
    CheckboxRootProps & {
        class?: HTMLAttributes['class'];
    }
>();

const emits = defineEmits<CheckboxRootEmits>();

const delegatedProps = reactiveOmit(props, 'class');
const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <CheckboxRoot
        v-bind="forwarded"
        :class="
            cn(
                'peer size-5 shrink-0 rounded-md border-2 border-border bg-surface ring-offset-surface transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus/30 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-55 data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-white',
                props.class,
            )
        "
    >
        <CheckboxIndicator class="flex items-center justify-center text-current">
            <Check class="size-4" />
        </CheckboxIndicator>
    </CheckboxRoot>
</template>
