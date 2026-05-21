<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bell, BellOff, X } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { usePatientMedicationPushReminders } from '@/composables/usePatientMedicationPushReminders';

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
</script>

<template>
    <section
        v-if="shouldShowDashboardPrompt"
        class="relative rounded-xl border border-border bg-surface p-4 shadow-sm sm:p-5"
    >
        <Button
            type="button"
            variant="ghost"
            size="icon"
            class="absolute top-3 right-3 size-10 shrink-0 text-text-muted hover:text-text"
            :aria-label="t('patient.medicationReminders.dismissPromptAriaLabel')"
            @click="dismissDashboardPrompt"
        >
            <X
                class="size-5"
                aria-hidden="true"
            />
        </Button>

        <div class="flex items-start gap-3 pe-10">
            <component
                :is="cardVariant === 'denied' ? BellOff : Bell"
                class="mt-1 size-6 shrink-0 text-primary"
                aria-hidden="true"
            />

            <div class="min-w-0 flex-1 space-y-4">
                <div class="space-y-2">
                    <h2 class="text-base font-semibold text-text-heading sm:text-lg">
                        {{ t('patient.medicationReminders.promptTitle') }}
                    </h2>

                    <p
                        v-if="cardVariant === 'missing_config'"
                        class="text-base leading-relaxed text-text-muted"
                    >
                        {{ t('patient.medicationReminders.missingConfig') }}
                    </p>

                    <p
                        v-else-if="cardVariant === 'denied'"
                        class="text-base leading-relaxed text-text-muted"
                    >
                        {{ t('patient.medicationReminders.deniedDescription') }}
                    </p>

                    <template v-else>
                        <p class="text-base leading-relaxed text-text-muted">
                            {{ t('patient.medicationReminders.promptDescription') }}
                        </p>

                        <p class="text-base leading-relaxed text-text-muted">
                            {{ t('patient.medicationReminders.installRequiredNote') }}
                        </p>

                        <ol
                            class="list-decimal space-y-2 pl-6 text-base leading-relaxed text-text-muted"
                            :aria-label="t('patient.medicationReminders.onboardingStepsAriaLabel')"
                        >
                            <li>{{ t('patient.medicationReminders.stepOpenNotification') }}</li>
                            <li>{{ t('patient.medicationReminders.stepConfirm') }}</li>
                            <li>{{ t('patient.medicationReminders.stepNoApp') }}</li>
                        </ol>
                    </template>
                </div>

                <Button
                    v-if="cardVariant === 'enable'"
                    type="button"
                    size="lg"
                    class="min-h-12 w-full text-base font-semibold sm:w-auto"
                    :disabled="isRegistering"
                    @click="enableReminders"
                >
                    {{ t('patient.medicationReminders.enableButton') }}
                </Button>

                <p class="text-base leading-relaxed text-text-muted">
                    {{ t('patient.medicationReminders.dismissPromptHint') }}
                    <Link
                        :href="settingsMedicationRemindersUrl"
                        class="font-medium text-primary underline-offset-2 hover:underline"
                        @click="dismissDashboardPrompt"
                    >
                        {{ t('patient.medicationReminders.settingsTitle') }}
                    </Link>
                </p>

                <p
                    v-if="registrationError"
                    class="text-base text-destructive"
                >
                    {{ registrationError }}
                </p>
            </div>
        </div>
    </section>
</template>
