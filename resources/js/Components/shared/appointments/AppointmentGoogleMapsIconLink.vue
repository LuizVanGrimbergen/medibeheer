<script setup lang="ts">
import { MapPin, Navigation } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/Components/ui/button';

const props = withDefaults(
    defineProps<{
        href: string;
        title: string;
        ariaLabel: string;
        icon?: 'map-pin' | 'route';
        stopPropagation?: boolean;
    }>(),
    {
        icon: 'map-pin',
        stopPropagation: false,
    },
);

const iconComponent = computed(() =>
    props.icon === 'route' ? Navigation : MapPin,
);

function handleClick(event: MouseEvent): void {
    if (props.stopPropagation) {
        event.stopPropagation();
    }
}
</script>

<template>
    <Button
        as-child
        size="icon-lg"
        class="shrink-0 touch-manipulation rounded-full [&_svg]:size-5"
    >
        <a
            :href="props.href"
            target="_blank"
            rel="noopener noreferrer"
            :title="props.title"
            :aria-label="props.ariaLabel"
            @click="handleClick"
        >
            <component
                :is="iconComponent"
                class="size-5 shrink-0"
                aria-hidden="true"
            />
        </a>
    </Button>
</template>
