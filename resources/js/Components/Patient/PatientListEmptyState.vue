<script setup lang="ts">
import { Card, CardContent } from '@/Components/ui/card';

defineProps<{
    message: string;
}>();
</script>

<template>
    <Card
        class="border-border bg-surface-2/70 text-text rounded-2xl border-2 border-dashed shadow-none"
    >
        <CardContent
            class="text-text-muted px-5 py-14 text-center text-lg leading-relaxed sm:px-8"
        >
            {{ message }}
        </CardContent>
    </Card>
</template>
