<script setup lang="ts">
import { Card, CardContent } from '@/Components/ui/card';
import {
    patientShellWizardCardClass,
    patientShellWizardCardInnerClass,
} from '@/lib/patient/patientShellDialogLayout';
</script>

<template>
    <Card :class="patientShellWizardCardClass">
        <CardContent class="p-0">
            <div :class="patientShellWizardCardInnerClass">
                <slot />
            </div>
        </CardContent>
    </Card>
</template>
