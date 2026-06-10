<script setup lang="ts">
import { Card, CardContent } from '@/Components/ui/card';
import {
    mobileShellWizardCardClass,
    mobileShellWizardCardInnerClass,
} from '@/lib/shell/mobileShellDialogLayout';
</script>

<template>
    <Card :class="mobileShellWizardCardClass">
        <CardContent class="p-0">
            <div :class="mobileShellWizardCardInnerClass">
                <slot />
            </div>
        </CardContent>
    </Card>
</template>
