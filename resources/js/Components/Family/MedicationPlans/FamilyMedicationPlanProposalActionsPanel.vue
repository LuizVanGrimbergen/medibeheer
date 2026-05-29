<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import FamilyMedicationPlanProposalSummaryFooter from '@/Components/Family/MedicationPlans/FamilyMedicationPlanProposalSummaryFooter.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

defineProps<{
    publishUrl: string;
    processing: boolean;
    canPublish: boolean;
    canAddAnother: boolean;
}>();

const emit = defineEmits<{
    cancel: [];
    addAnother: [];
}>();

const { t } = useI18n();
</script>

<template>
    <Card class="rounded-2xl border-border bg-surface shadow-sm">
        <CardHeader class="border-b border-border px-5 py-4 sm:px-6">
            <CardTitle class="text-lg font-bold text-text-heading">
                {{ t('family.medicationPlans.actionsTitle') }}
            </CardTitle>
        </CardHeader>
        <CardContent class="px-5 py-5 sm:px-6">
            <FamilyMedicationPlanProposalSummaryFooter
                standalone
                :publish-url="publishUrl"
                :processing="processing"
                :can-publish="canPublish"
                :can-add-another="canAddAnother"
                @cancel="emit('cancel')"
                @add-another="emit('addAnother')"
            />
        </CardContent>
    </Card>
</template>
