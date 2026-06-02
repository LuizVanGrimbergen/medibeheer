<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import { computed, useId } from 'vue';
import { Button } from '@/Components/ui/button';

const props = defineProps<{
    title: string;
    description?: string;
    cta?: string;
    href: string;
    icon?: LucideIcon;
}>();

const titleId = useId();

const buttonLabel = computed(() => props.cta ?? props.title);
const showsHeadingBlock = computed(() => props.description !== undefined || props.cta !== undefined);
</script>

<template>
    <div
        class="flex flex-col gap-4"
        :aria-labelledby="titleId"
    >
        <div
            v-if="showsHeadingBlock"
            class="flex items-start gap-2.5 sm:gap-3"
        >
            <component
                :is="icon"
                v-if="icon"
                class="mt-0.5 size-6 shrink-0 text-primary sm:mt-0 sm:size-7"
                aria-hidden="true"
            />
            <div class="min-w-0 space-y-1 sm:space-y-1.5">
                <h3
                    :id="titleId"
                    class="daily-checkin-mood-step-title"
                >
                    {{ title }}
                </h3>
                <p
                    v-if="description"
                    class="daily-checkin-mood-step-description"
                >
                    {{ description }}
                </p>
            </div>
        </div>

        <Button
            as-child
            size="lg"
            class="w-full touch-manipulation sm:w-auto"
        >
            <Link
                :id="showsHeadingBlock ? undefined : titleId"
                :href="href"
            >
                {{ buttonLabel }}
            </Link>
        </Button>
    </div>
</template>
