<script setup lang="ts">
import { Card, CardContent } from '@/Components/ui/card';
import {
    mobileShellWizardFooterCardClass,
    mobileShellWizardFooterCardInnerClass,
} from '@/lib/shell/mobileShellDialogLayout';
</script>

<template>
    <Card :class="mobileShellWizardFooterCardClass">
        <CardContent :class="mobileShellWizardFooterCardInnerClass">
            <slot />
        </CardContent>
    </Card>
</template>
