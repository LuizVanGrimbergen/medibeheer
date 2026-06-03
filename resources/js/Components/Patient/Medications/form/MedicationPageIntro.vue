<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ClipboardList, Pill } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import {
    patientPageActionsBarClass,
    patientPageActionsGridClass,
    patientPageIntroButtonClass,
} from '@/lib/patient/patientPageTypography';

const props = defineProps<{
    canCreateMedication: boolean;
    hasActiveMedications: boolean;
}>();

const emit = defineEmits<{
    newMedicationClick: [];
}>();

const { t } = useI18n();

const hasActions = computed(
    () => props.canCreateMedication || props.hasActiveMedications,
);
</script>

<template>
    <div v-if="hasActions" :class="patientPageActionsBarClass">
        <div :class="patientPageActionsGridClass">
            <Button
                v-if="canCreateMedication"
                size="lg"
                :class="patientPageIntroButtonClass"
                type="button"
                @click="emit('newMedicationClick')"
            >
                <Pill class="size-6 shrink-0" aria-hidden="true" />
                {{ t('patient.medications.newMedication') }}
            </Button>

            <Button
                v-if="hasActiveMedications"
                as-child
                variant="outline"
                size="lg"
                :class="patientPageIntroButtonClass"
            >
                <Link :href="route('patient.medications.pharmacist-overview')">
                    <ClipboardList class="size-6 shrink-0" aria-hidden="true" />
                    {{ t('patient.medications.pharmacistOverview.button') }}
                </Link>
            </Button>
        </div>
    </div>
</template>
