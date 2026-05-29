<script setup lang="ts">
import { useVModel } from '@vueuse/core';
import type { HTMLAttributes } from 'vue';
import { useId } from 'vue';
import { Switch } from '@/Components/ui/switch';
import { cn } from '@/lib/utils';

const props = defineProps<{
    modelValue: boolean;
    label: string;
    description?: string;
    disabled?: boolean;
    class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

const model = useVModel(props, 'modelValue', emit);
const switchId = useId();
</script>

<template>
    <div
        :class="
            cn(
                'flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-6',
                props.class,
            )
        "
    >
        <div class="min-w-0 flex-1 space-y-1.5 sm:pt-0.5">
            <label
                :for="switchId"
                class="block cursor-pointer text-base font-semibold leading-snug text-text-heading"
            >
                {{ label }}
            </label>
            <p
                v-if="description"
                class="text-sm leading-relaxed text-text-muted sm:text-base"
            >
                {{ description }}
            </p>
        </div>
        <Switch
            :id="switchId"
            v-model="model"
            :disabled="disabled"
            class="shrink-0 self-end sm:mt-0.5 sm:self-start"
        />
    </div>
</template>
