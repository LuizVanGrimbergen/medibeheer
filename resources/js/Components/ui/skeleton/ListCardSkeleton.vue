<script setup lang="ts">
import { Card, CardContent } from '@/Components/ui/card';

withDefaults(
    defineProps<{
        count?: number;
        gapClass?: string;
    }>(),
    {
        count: 3,
        gapClass: 'gap-5',
    },
);
</script>

<template>
    <ul
        class="flex w-full min-w-0 flex-col"
        :class="gapClass"
        aria-hidden="true"
    >
        <li v-for="index in count" :key="index" class="min-w-0">
            <Card
                class="border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] sm:rounded-3xl"
            >
                <CardContent class="space-y-4 p-5 sm:p-6">
                    <div
                        class="bg-surface-2 h-7 w-2/3 max-w-xs animate-pulse rounded-lg"
                    />
                    <div
                        class="bg-surface-2 h-5 w-1/2 max-w-48 animate-pulse rounded-lg"
                    />
                    <div
                        class="bg-surface-2 h-16 w-full animate-pulse rounded-xl"
                    />
                </CardContent>
            </Card>
        </li>
    </ul>
</template>
