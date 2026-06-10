<script setup lang="ts">
import { mobileShellPageCardDetailsGroupClass } from '@/lib/shell/mobileShellTypography';
</script>

<template>
    <div :class="mobileShellPageCardDetailsGroupClass">
        <slot />
    </div>
</template>
