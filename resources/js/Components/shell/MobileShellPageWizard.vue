<script setup lang="ts">
import { useMobileShellPageChrome } from '@/composables/patient/useMobileShellDialogChrome';
import {
    mobileShellDialogDescriptionClass,
    mobileShellDialogHeaderClass,
    mobileShellPageRootClass,
    mobileShellDialogTitleClass,
} from '@/lib/shell/mobileShellDialogLayout';

defineProps<{
    title: string;
    description?: string;
}>();

useMobileShellPageChrome();
</script>

<template>
    <div :class="mobileShellPageRootClass">
        <header :class="mobileShellDialogHeaderClass">
            <h1 :class="mobileShellDialogTitleClass">
                {{ title }}
            </h1>
            <p
                v-if="description !== undefined && description !== ''"
                :class="mobileShellDialogDescriptionClass"
            >
                {{ description }}
            </p>
        </header>

        <slot />
    </div>
</template>
