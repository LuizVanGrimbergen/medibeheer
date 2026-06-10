<script setup lang="ts">
import PatientListEmptyState from '@/Components/Patient/PatientListEmptyState.vue';
import ListCardSkeleton from '@/Components/ui/skeleton/ListCardSkeleton.vue';

withDefaults(
    defineProps<{
        loading: boolean;
        showEmpty?: boolean;
        emptyMessage?: string;
    }>(),
    {
        showEmpty: false,
        emptyMessage: '',
    },
);
</script>

<template>
    <section class="space-y-5" :aria-busy="loading">
        <slot name="heading" />

        <ListCardSkeleton v-if="loading" />

        <template v-else>
            <slot />
            <slot name="pagination" />
            <PatientListEmptyState
                v-if="showEmpty"
                :message="emptyMessage"
            />
        </template>
    </section>
</template>
