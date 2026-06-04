<script setup lang="ts">
import { Stethoscope } from 'lucide-vue-next';
import { useAppointmentDisplay } from '@/Components/Appointments/useAppointmentDisplay';
import PatientAppointmentScheduleDetailRows from '@/Components/Patient/Appointments/PatientAppointmentScheduleDetailRows.vue';
import { Card, CardContent } from '@/Components/ui/card';
import {
    patientShellWizardCardClass,
    patientShellWizardCardInnerClass,
} from '@/lib/patient/patientShellDialogLayout';
import type { Appointment } from '@/lib/types';

const props = defineProps<{
    appointment: Appointment;
}>();

const { doctorTypeLabel } = useAppointmentDisplay();
</script>

<template>
    <Card :class="patientShellWizardCardClass">
        <CardContent class="p-0">
            <div
                :class="[patientShellWizardCardInnerClass, 'space-y-5']"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="bg-primary/12 flex size-12 shrink-0 items-center justify-center rounded-xl"
                        aria-hidden="true"
                    >
                        <Stethoscope class="text-primary size-6" />
                    </div>
                    <div class="min-w-0 flex-1 space-y-1">
                        <p
                            class="text-text-heading text-lg leading-snug font-bold sm:text-xl"
                        >
                            {{
                                props.appointment.doctor_type
                                    ? doctorTypeLabel(
                                          props.appointment.doctor_type,
                                      )
                                    : props.appointment.provider_name
                            }}
                        </p>
                        <p
                            v-if="props.appointment.doctor_type"
                            class="text-text-muted text-base leading-snug font-normal"
                        >
                            {{ props.appointment.provider_name }}
                        </p>
                    </div>
                </div>

                <PatientAppointmentScheduleDetailRows
                    :appointment="props.appointment"
                />
            </div>
        </CardContent>
    </Card>
</template>
