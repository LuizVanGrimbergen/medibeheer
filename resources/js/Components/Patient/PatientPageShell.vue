<script setup lang="ts">
defineProps<{
    title: string;
}>();
</script>

<template>
    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 md:gap-5">
        <h1 class="sr-only">
            {{ title }}
        </h1>

        <slot />
    </div>
</template>
