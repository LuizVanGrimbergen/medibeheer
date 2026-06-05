<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bell, BellOff, X } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { useFamilyMedicationPushReminders } from '@/composables/family/useFamilyMedicationPushReminders';

const { t } = useI18n();

const {
    shouldShowDashboardPrompt,
    cardVariant,
    isRegistering,
    registrationError,
    enableReminders,
    dismissDashboardPrompt,
    translationPrefix,
} = useFamilyMedicationPushReminders();

const tKey = (key: string): string => t(`${translationPrefix}.${key}`);

const settingsMedicationRemindersUrl = route('settings.edit', {
    section: 'medication-reminders',
});
</script>

<template>
    <section
        v-if="shouldShowDashboardPrompt"
        class="border-border bg-surface relative rounded-xl border p-4 shadow-sm sm:p-5"
    >
        <Button
            type="button"
            variant="ghost"
            size="icon"
            class="text-text-muted hover:text-text absolute top-3 right-3 size-10 shrink-0"
            :aria-label="tKey('dismissPromptAriaLabel')"
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
                    <h2
                        class="text-text-heading text-base font-semibold sm:text-lg"
                    >
                        {{ tKey('promptTitle') }}
                    </h2>

                    <p
                        v-if="cardVariant === 'missing_config'"
                        class="text-text-muted text-base leading-relaxed"
                    >
                        {{ tKey('missingConfig') }}
                    </p>

                    <p
                        v-else-if="cardVariant === 'denied'"
                        class="text-text-muted text-base leading-relaxed"
                    >
                        {{ tKey('deniedDescription') }}
                    </p>

                    <template v-else>
                        <p class="text-text-muted text-base leading-relaxed">
                            {{ tKey('promptDescription') }}
                        </p>

                        <p class="text-text-muted text-base leading-relaxed">
                            {{ tKey('installRequiredNote') }}
                        </p>
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
                    {{ tKey('enableButton') }}
                </Button>

                <p class="text-text-muted text-base leading-relaxed">
                    {{ tKey('dismissPromptHint') }}
                    <Link
                        :href="settingsMedicationRemindersUrl"
                        class="text-primary font-medium underline-offset-2 hover:underline"
                        @click="dismissDashboardPrompt"
                    >
                        {{ tKey('settingsTitle') }}
                    </Link>
                </p>

                <p v-if="registrationError" class="text-destructive text-base">
                    {{ registrationError }}
                </p>
            </div>
        </div>
    </section>
</template>
