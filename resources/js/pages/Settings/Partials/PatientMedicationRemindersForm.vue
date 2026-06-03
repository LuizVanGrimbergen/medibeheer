<script setup lang="ts">
import { Bell, BellOff } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { usePatientMedicationPushReminders } from '@/composables/patient/usePatientMedicationPushReminders';

const { t } = useI18n();

const {
    browserSupportsPush,
    canEnableReminders,
    remindersEnabled,
    cardVariant,
    isRegistering,
    isUnregistering,
    registrationError,
    enableReminders,
    disableReminders,
} = usePatientMedicationPushReminders();
</script>

<template>
    <section class="space-y-6">
        <div class="flex items-start gap-3">
            <component
                :is="remindersEnabled ? Bell : BellOff"
                class="text-primary mt-0.5 size-6 shrink-0"
                aria-hidden="true"
            />

            <div class="min-w-0 flex-1 space-y-4">
                <div class="space-y-2">
                    <h2 class="text-text text-lg font-semibold">
                        {{ t('patient.medicationReminders.settingsTitle') }}
                    </h2>

                    <p
                        v-if="cardVariant === 'missing_config'"
                        class="text-text-muted text-base leading-relaxed"
                    >
                        {{ t('patient.medicationReminders.missingConfig') }}
                    </p>

                    <p
                        v-else-if="cardVariant === 'denied'"
                        class="text-text-muted text-base leading-relaxed"
                    >
                        {{ t('patient.medicationReminders.deniedDescription') }}
                    </p>

                    <template v-else>
                        <p class="text-text-muted text-base leading-relaxed">
                            {{
                                t(
                                    'patient.medicationReminders.settingsDescription',
                                )
                            }}
                        </p>

                        <p
                            v-if="remindersEnabled"
                            class="text-text text-base font-medium"
                        >
                            {{
                                t(
                                    'patient.medicationReminders.settingsEnabledStatus',
                                )
                            }}
                        </p>

                        <p
                            v-else
                            class="text-text-muted text-base leading-relaxed"
                        >
                            {{
                                t(
                                    'patient.medicationReminders.settingsDisabledStatus',
                                )
                            }}
                        </p>

                        <p
                            v-if="
                                !remindersEnabled &&
                                browserSupportsPush &&
                                canEnableReminders
                            "
                            class="text-text-muted text-base leading-relaxed"
                        >
                            {{
                                t(
                                    'patient.medicationReminders.installRequiredNote',
                                )
                            }}
                        </p>
                    </template>
                </div>

                <Button
                    v-if="
                        remindersEnabled &&
                        cardVariant !== 'missing_config' &&
                        cardVariant !== 'denied'
                    "
                    type="button"
                    variant="outline"
                    size="lg"
                    class="min-h-12 w-full text-base font-semibold sm:w-auto"
                    :disabled="isUnregistering"
                    @click="disableReminders"
                >
                    {{ t('patient.medicationReminders.disableButton') }}
                </Button>

                <Button
                    v-else-if="cardVariant === 'enable'"
                    type="button"
                    size="lg"
                    class="min-h-12 w-full text-base font-semibold sm:w-auto"
                    :disabled="isRegistering"
                    @click="enableReminders"
                >
                    {{ t('patient.medicationReminders.enableButton') }}
                </Button>

                <p v-if="registrationError" class="text-destructive text-base">
                    {{ registrationError }}
                </p>
            </div>
        </div>
    </section>
</template>
