<script setup lang="ts">
import {
    mobileShellPageIntroClass,
    mobileShellPageTitleClass,
} from '@/lib/shell/mobileShellTypography';
import { mobileShellPageContentClass } from '@/lib/shell/mobileShellLayout';

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
    <div :class="mobileShellPageContentClass">
        <header v-if="showVisibleTitle || intro !== ''" class="space-y-3">
            <h1 :class="showVisibleTitle ? mobileShellPageTitleClass : 'sr-only'">
                {{ title }}
            </h1>
            <p v-if="intro !== ''" :class="mobileShellPageIntroClass">
                {{ intro }}
            </p>
        </header>

        <h1 v-else class="sr-only">
            {{ title }}
        </h1>

        <slot />
    </div>
</template>
