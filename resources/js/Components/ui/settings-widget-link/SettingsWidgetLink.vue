<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { Card, CardContent } from '@/Components/ui/card';
import { cn } from '@/lib/utils';

withDefaults(
    defineProps<{
        href: string;
        roundedClass?: string;
    }>(),
    {
        roundedClass: 'rounded-2xl',
    },
);
</script>

<template>
    <Link :href="href" class="group block">
        <Card
            :class="
                cn(
                    'border-border bg-surface shadow-sm transition group-hover:bg-surface-2',
                    roundedClass,
                )"
        >
            <CardContent class="p-4 sm:p-6">
                <div class="flex items-center gap-4">
                    <slot name="leading" />

                    <div class="min-w-0 flex-1">
                        <slot />
                    </div>

                    <ChevronRight
                        :size="18"
                        class="shrink-0 text-text-muted transition group-hover:text-text"
                        aria-hidden="true"
                    />
                </div>
            </CardContent>
        </Card>
    </Link>
</template>
