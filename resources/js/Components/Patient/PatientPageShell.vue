<script setup lang="ts">
withDefaults(
    defineProps<{
        title: string;
        intro?: string;
        showVisibleTitle?: boolean;
    }>(),
    {
        intro: '',
        showVisibleTitle: false,
    },
);
</script>

<template>
    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 md:gap-5">
        <header
            v-if="showVisibleTitle || intro !== ''"
            class="space-y-3"
        >
            <h1
                :class="
                    showVisibleTitle
                        ? 'text-2xl font-bold leading-tight text-text-heading md:text-3xl'
                        : 'sr-only'
                "
            >
                {{ title }}
            </h1>
            <p
                v-if="intro !== ''"
                class="text-base leading-relaxed text-text-muted md:text-lg"
            >
                {{ intro }}
            </p>
        </header>

        <h1
            v-else
            class="sr-only"
        >
            {{ title }}
        </h1>

        <slot />
    </div>
</template>
