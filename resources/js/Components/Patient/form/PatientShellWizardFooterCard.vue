<script setup lang="ts">
import { Card, CardContent } from '@/Components/ui/card';
import {
    patientShellWizardFooterCardClass,
    patientShellWizardFooterCardInnerClass,
} from '@/lib/patient/patientShellDialogLayout';
</script>

<template>
    <Card :class="patientShellWizardFooterCardClass">
        <CardContent :class="patientShellWizardFooterCardInnerClass">
            <slot />
        </CardContent>
    </Card>
</template>
