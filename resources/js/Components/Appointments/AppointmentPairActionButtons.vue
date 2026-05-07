<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        disabled?: boolean;
        showSecondary?: boolean;
        primaryClass?: string;
        secondaryClass?: string;
    }>(),
    {
        disabled: false,
        showSecondary: true,
        primaryClass: '',
        secondaryClass: '',
    },
);

defineEmits<{
    'primary-click': [];
    'secondary-click': [];
}>();

const primaryLayoutClass = computed(() =>
    props.showSecondary
        ? 'min-h-14 min-w-0 flex-1 touch-manipulation gap-2.5 px-4 text-lg font-semibold [&_svg]:size-6'
        : 'min-h-14 w-full touch-manipulation gap-2.5 px-4 text-lg font-semibold sm:w-auto [&_svg]:size-6',
);

const secondaryLayoutClass =
    'min-h-14 min-w-0 flex-1 touch-manipulation gap-2.5 border-2 border-danger/50 px-4 text-lg font-semibold text-danger hover:border-danger hover:bg-danger/10 hover:text-danger [&_svg]:size-6';
</script>

<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:gap-3">
        <Button
            type="button"
            variant="default"
            size="lg"
            :disabled="disabled"
            :class="cn(primaryLayoutClass, primaryClass)"
            @click="$emit('primary-click')"
        >
            <slot name="primary" />
        </Button>
        <Button
            v-if="showSecondary"
            type="button"
            variant="outline"
            size="lg"
            :disabled="disabled"
            :class="cn(secondaryLayoutClass, secondaryClass)"
            @click="$emit('secondary-click')"
        >
            <slot name="secondary" />
        </Button>
    </div>
</template>
