<script setup lang="ts">
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';

const emit = defineEmits<{
    enter: [];
    'update:modelValue': [value: string];
}>();

withDefaults(
    defineProps<{
        inputId?: string;
        modelValue: string;
        label: string;
        placeholder: string;
        error?: string;
    }>(),
    {
        inputId: 'password',
        error: '',
    },
);
</script>

<template>
    <div class="mt-6">
        <Label :for="inputId" class="sr-only">{{ label }}</Label>
        <Input
            :id="inputId"
            :model-value="modelValue"
            type="password"
            class="mt-1 h-auto w-full rounded-xl border-border bg-surface px-4 py-3 text-xl text-text placeholder:text-text-muted focus-visible:ring-focus/20"
            :placeholder="placeholder"
            autocomplete="current-password"
            @update:model-value="(value) => emit('update:modelValue', String(value))"
            @keyup.enter="emit('enter')"
        />
        <InputError
            :message="error"
            class="mt-2"
        />
    </div>
</template>
