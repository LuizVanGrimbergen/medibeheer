<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    patientName: string;
}>();

defineEmits<{
    switchPatient: [];
}>();

const { t } = useI18n();

const heading = computed((): string =>
    t('doctor.patients.overviewHeading', {
        name: props.patientName,
    }),
);
</script>

<template>
    <header
        class="border-border bg-surface flex min-w-0 items-center justify-between gap-4 rounded-2xl border px-5 py-4 shadow-sm md:px-6"
    >
        <div class="min-w-0">
            <h1
                class="text-text-heading truncate text-2xl font-semibold md:text-xl"
            >
                {{ heading }}
            </h1>
        </div>

        <button
            type="button"
            class="border-border text-text hover:bg-surface-2 focus-visible:ring-focus shrink-0 rounded-xl border px-4 py-2 text-sm font-semibold transition focus-visible:ring-2 focus-visible:outline-none"
            @click="$emit('switchPatient')"
        >
            {{ t('doctor.patients.contextSwitchPatient') }}
        </button>
    </header>
</template>
