<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CalendarPlus, FileText } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button, buttonVariants } from '@/Components/ui/button';
import {
    patientPageActionsBarClass,
    patientPageActionsGridClass,
    patientPageIntroButtonClass,
} from '@/lib/patient/patientPageTypography';
import { cn } from '@/lib/utils';

defineProps<{
    canAddPrescription: boolean;
}>();

const emit = defineEmits<{
    addPrescriptionClick: [];
}>();

const { t } = useI18n();
</script>

<template>
    <div :class="patientPageActionsBarClass">
        <div :class="patientPageActionsGridClass">
            <Button
                v-if="canAddPrescription"
                size="lg"
                :class="patientPageIntroButtonClass"
                type="button"
                @click="emit('addPrescriptionClick')"
            >
                <FileText class="size-6 shrink-0" aria-hidden="true" />
                {{ t('patient.prescriptions.addPrescription') }}
            </Button>

            <Link
                :href="route('patient.appointments', { open_create: 1 })"
                :class="
                    cn(
                        buttonVariants({ variant: 'outline', size: 'lg' }),
                        patientPageIntroButtonClass,
                    )
                "
            >
                <CalendarPlus class="size-6 shrink-0" aria-hidden="true" />
                {{ t('patient.appointments.newAppointment') }}
            </Link>
        </div>
    </div>
</template>
