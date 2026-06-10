<script setup lang="ts">
import { Package, PillBottle } from 'lucide-vue-next';

defineProps<{
    hint: string;
    usesLiquidIcon: boolean;
}>();
</script>

<template>
    <div
        class="border-border/60 bg-surface-2/30 dark:border-border/70 dark:bg-surface-2/50 flex w-full min-w-0 items-start gap-3.5 rounded-2xl border px-4 py-3.5 sm:gap-4 sm:rounded-3xl sm:px-5 sm:py-4"
    >
        <div
            class="bg-primary/12 text-primary flex size-11 shrink-0 items-center justify-center rounded-xl sm:size-14 sm:rounded-2xl"
        >
            <PillBottle
                v-if="usesLiquidIcon"
                class="size-5 sm:size-6"
                aria-hidden="true"
            />
            <Package v-else class="size-5 sm:size-6" aria-hidden="true" />
        </div>
        <div class="flex min-w-0 flex-1 flex-col gap-1">
            <span
                class="text-text-heading text-sm leading-snug font-semibold sm:text-base"
            >
                {{ hint }}
            </span>
        </div>
    </div>
</template>
