<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
    selectedDate: string | null;
    heading: string;
}>();

const sectionRef = ref<HTMLElement | null>(null);

function scrollIntoView(options?: ScrollIntoViewOptions): void {
    sectionRef.value?.scrollIntoView(options);
}

defineExpose({
    scrollIntoView,
});
</script>

<template>
    <section
        v-if="selectedDate !== null"
        ref="sectionRef"
        class="scroll-mt-24 space-y-3"
        tabindex="-1"
    >
        <h2 class="text-lg font-semibold text-text-heading">
            {{ heading }}
        </h2>
        <slot />
    </section>
</template>
