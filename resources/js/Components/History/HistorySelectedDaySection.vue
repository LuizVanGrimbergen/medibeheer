<script setup lang="ts">
defineProps<{
    selectedDate: string | null;
    heading: string;
}>();
</script>

<template>
    <section
        v-if="selectedDate !== null"
        class="scroll-mt-24 space-y-3"
        tabindex="-1"
    >
        <h2 class="text-lg font-semibold text-text-heading">
            {{ heading }}
        </h2>
        <slot />
    </section>
</template>
