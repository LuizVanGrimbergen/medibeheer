<script setup lang="ts">
defineProps<{
    label: string;
}>();
</script>

<template>
    <div class="flex gap-4 sm:gap-5">
        <slot name="icon" />
        <div class="min-w-0 flex-1 space-y-1.5">
            <p class="text-base font-semibold leading-tight text-text-heading">
                {{ label }}
            </p>
            <p class="text-lg font-medium leading-relaxed tracking-tight text-text sm:text-xl sm:leading-snug">
                <slot />
            </p>
        </div>
    </div>
</template>
