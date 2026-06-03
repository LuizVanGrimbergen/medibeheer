<script setup lang="ts">
import { CalendarPlus } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import {
    patientPageActionsBarClass,
    patientPageActionsGridClass,
    patientPageIntroButtonClass,
} from '@/lib/patient/patientPageTypography';

const emit = defineEmits<{
    newAppointmentClick: [];
}>();

const { t } = useI18n();
</script>

<template>
    <div :class="patientPageActionsBarClass">
        <div :class="patientPageActionsGridClass">
            <Button
                size="lg"
                :class="patientPageIntroButtonClass"
                type="button"
                @click="emit('newAppointmentClick')"
            >
                <CalendarPlus class="size-6 shrink-0" aria-hidden="true" />
                {{ t('patient.appointments.newAppointment') }}
            </Button>
        </div>
    </div>
</template>
