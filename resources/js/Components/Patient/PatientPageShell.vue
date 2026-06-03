<script setup lang="ts">
import {
    patientPageIntroClass,
    patientPageTitleClass,
} from '@/lib/patient/patientPageTypography';

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
                        ? patientPageTitleClass
                        : 'sr-only'
                "
            >
                {{ title }}
            </h1>
            <p
                v-if="intro !== ''"
                :class="patientPageIntroClass"
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
