<script setup lang="ts">
import { FileText } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import {
    patientPageActionsBarClass,
    patientPageActionsGridClass,
    patientPageIntroButtonClass,
} from '@/lib/patient/patientPageTypography';

defineProps<{
    canAddPrescription: boolean;
}>();

const emit = defineEmits<{
    addPrescriptionClick: [];
}>();

const { t } = useI18n();
</script>

<template>
    <div v-if="canAddPrescription" :class="patientPageActionsBarClass">
        <div :class="patientPageActionsGridClass">
            <Button
                size="lg"
                :class="patientPageIntroButtonClass"
                type="button"
                @click="emit('addPrescriptionClick')"
            >
                <FileText class="size-6 shrink-0" aria-hidden="true" />
                {{ t('patient.prescriptions.addPrescription') }}
            </Button>
        </div>
    </div>
</template>
