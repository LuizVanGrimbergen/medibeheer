<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Bell, BellOff, X } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import MedicalDisclaimer from '@/Components/Legal/MedicalDisclaimer.vue';
import { Button, buttonVariants } from '@/Components/ui/button';
import { InputError } from '@/Components/ui/input-error';
import { usePatientMedicationPushReminders } from '@/composables/patient/usePatientMedicationPushReminders';
import { mobileShellDashboardPromptCardClass } from '@/lib/shell/mobileShellLayout';
import {
    mobileShellPageIntroButtonClass,
    mobileShellSectionBodyTextClass,
    mobileShellSectionSubHeadingClass,
} from '@/lib/shell/mobileShellTypography';
import { cn } from '@/lib/utils';

const { t } = useI18n();

const {
    shouldShowDashboardPrompt,
    cardVariant,
    isRegistering,
    registrationError,
    enableReminders,
    dismissDashboardPrompt,
} = usePatientMedicationPushReminders();

const settingsMedicationRemindersUrl = route('settings.edit', {
    section: 'medication-reminders',
});

const registrationErrorId = 'patient-medication-reminder-registration-error';

const hasRegistrationError = computed(
    () =>
        registrationError.value !== null &&
        registrationError.value.trim() !== '',
);
</script>

<template>
    <section
        v-if="shouldShowDashboardPrompt"
        :class="mobileShellDashboardPromptCardClass"
    >
        <Button
            type="button"
            variant="ghost"
            size="icon"
            class="text-text-muted hover:text-text absolute top-3 right-3 size-10 shrink-0"
            :aria-label="
                t('patient.medicationReminders.dismissPromptAriaLabel')
            "
            @click="dismissDashboardPrompt"
        >
            <X class="size-5" aria-hidden="true" />
        </Button>

        <div class="flex items-start gap-3 pe-10">
            <component
                :is="cardVariant === 'denied' ? BellOff : Bell"
                class="text-primary mt-1 size-6 shrink-0"
                aria-hidden="true"
            />

            <div class="min-w-0 flex-1 space-y-4">
                <div class="space-y-2">
                    <h2 :class="mobileShellSectionSubHeadingClass">
                        {{ t('patient.medicationReminders.promptTitle') }}
                    </h2>

                    <p
                        v-if="cardVariant === 'missing_config'"
                        :class="mobileShellSectionBodyTextClass"
                    >
                        {{ t('patient.medicationReminders.missingConfig') }}
                    </p>

                    <p
                        v-else-if="cardVariant === 'denied'"
                        :class="mobileShellSectionBodyTextClass"
                    >
                        {{ t('patient.medicationReminders.deniedDescription') }}
                    </p>

                    <template v-else>
                        <p :class="mobileShellSectionBodyTextClass">
                            {{
                                t(
                                    'patient.medicationReminders.promptDescription',
                                )
                            }}
                        </p>

                        <p :class="mobileShellSectionBodyTextClass">
                            {{
                                t(
                                    'patient.medicationReminders.installRequiredNote',
                                )
                            }}
                        </p>

                        <ol
                            class="text-text-muted list-decimal space-y-2 pl-6 text-base leading-relaxed"
                            :aria-label="
                                t(
                                    'patient.medicationReminders.onboardingStepsAriaLabel',
                                )
                            "
                        >
                            <li>
                                {{
                                    t(
                                        'patient.medicationReminders.stepOpenNotification',
                                    )
                                }}
                            </li>
                            <li>
                                {{
                                    t('patient.medicationReminders.stepConfirm')
                                }}
                            </li>
                            <li>
                                {{ t('patient.medicationReminders.stepNoApp') }}
                            </li>
                        </ol>
                    </template>
                </div>

                <Button
                    v-if="cardVariant === 'enable'"
                    type="button"
                    :class="
                        cn(
                            buttonVariants({ variant: 'default', size: 'lg' }),
                            mobileShellPageIntroButtonClass,
                            'sm:w-auto',
                        )
                    "
                    :disabled="isRegistering"
                    :aria-describedby="
                        hasRegistrationError ? registrationErrorId : undefined
                    "
                    @click="enableReminders"
                >
                    {{ t('patient.medicationReminders.enableButton') }}
                </Button>

                <MedicalDisclaimer message-key="legal.disclaimer.pushReminder" />

                <p :class="mobileShellSectionBodyTextClass">
                    {{ t('patient.medicationReminders.dismissPromptHint') }}
                    <Link
                        :href="settingsMedicationRemindersUrl"
                        class="text-primary font-medium underline-offset-2 hover:underline"
                        @click="dismissDashboardPrompt"
                    >
                        {{ t('patient.medicationReminders.settingsTitle') }}
                    </Link>
                </p>

                <InputError
                    :id="registrationErrorId"
                    :message="registrationError ?? undefined"
                />
            </div>
        </div>
    </section>
</template>
