<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';
import { Button } from '@/Components/ui/button';
import { mobileShellFormPrimaryPairButtonClass } from '@/lib/shell/mobileShellActionButtonClasses';

const open = defineModel<boolean>('open', { required: true });

defineProps<{
    isLastPrescription: boolean;
}>();

const { t } = useI18n();

const doneButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl px-3 text-base font-semibold sm:min-h-14 sm:flex-1 sm:px-4 sm:text-base md:text-lg';

function dismiss(): void {
    open.value = false;
}

function goToNewAppointment(): void {
    open.value = false;
    router.get(route('patient.appointments', { open_create: 1 }));
}
</script>

<template>
    <PatientActionSuccessScreen
        v-model:open="open"
        :title="t('patient.actionSuccess.prescriptions.pickedUp.title')"
        :subtitle="
            isLastPrescription
                ? t(
                      'patient.actionSuccess.prescriptions.pickedUp.lastPrescriptionSubtitle',
                  )
                : null
        "
        :done-label="t('patient.actionSuccess.done')"
    >
        <template v-if="isLastPrescription" #footer>
            <div
                class="flex w-full min-w-0 flex-col gap-3 sm:flex-row-reverse sm:gap-3"
            >
                <Button
                    type="button"
                    variant="default"
                    size="lg"
                    :class="mobileShellFormPrimaryPairButtonClass"
                    @click="goToNewAppointment"
                >
                    {{
                        t(
                            'patient.actionSuccess.prescriptions.pickedUp.scheduleAppointment',
                        )
                    }}
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="lg"
                    :class="doneButtonClass"
                    @click="dismiss"
                >
                    {{ t('patient.actionSuccess.done') }}
                </Button>
            </div>
        </template>
    </PatientActionSuccessScreen>
</template>
