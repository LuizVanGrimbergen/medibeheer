<script setup lang="ts">
import { Search, X } from 'lucide-vue-next';
import { computed } from 'vue';
import type { HTMLAttributes } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { cn } from '@/lib/utils';

const modelValue = defineModel<string>({ required: true });

const props = withDefaults(
    defineProps<{
        id: string;
        name?: string;
        placeholder: string;
        clearLabel: string;
        ariaLabel?: string;
        autofocus?: boolean;
        autocomplete?: string;
        class?: HTMLAttributes['class'];
    }>(),
    {
        name: undefined,
        ariaLabel: undefined,
        autofocus: false,
        autocomplete: 'off',
    },
);

const hasValue = computed(() => modelValue.value.trim() !== '');

const resolvedAriaLabel = computed(() => props.ariaLabel ?? props.placeholder);

function clear(): void {
    modelValue.value = '';
}
</script>

<template>
    <div :class="cn('relative', props.class)">
        <Search
            class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-text-muted"
            aria-hidden="true"
        />
        <Input
            :id="props.id"
            v-model="modelValue"
            type="search"
            :name="props.name ?? props.id"
            :autocomplete="props.autocomplete"
            :autofocus="props.autofocus"
            :placeholder="props.placeholder"
            :aria-label="resolvedAriaLabel"
            class="h-auto rounded-xl border-border bg-surface py-3 pl-12 pr-12 text-base text-text placeholder:text-text-muted focus-visible:ring-primary/30"
        />
        <Button
            v-if="hasValue"
            type="button"
            variant="ghost"
            size="icon-sm"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-heading"
            :aria-label="props.clearLabel"
            @click="clear"
        >
            <X
                class="size-5"
                aria-hidden="true"
            />
        </Button>
    </div>
</template>
