<script setup lang="ts">
defineProps<{
    label: string;
}>();
</script>

<template>
    <div class="flex gap-4 sm:gap-5">
        <slot name="icon" />
        <div class="min-w-0 flex-1 space-y-1.5">
            <p class="text-text-heading text-base leading-tight font-semibold">
                {{ label }}
            </p>
            <p
                class="text-text text-lg leading-relaxed font-medium tracking-tight sm:text-xl sm:leading-snug"
            >
                <slot />
            </p>
        </div>
    </div>
</template>
