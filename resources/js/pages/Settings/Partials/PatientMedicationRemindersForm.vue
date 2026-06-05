<script setup lang="ts">
import { Bell, BellOff } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { useMedicationPushReminders } from '@/composables/useMedicationPushReminders';

const props = withDefaults(
    defineProps<{
        role?: 'patient' | 'family_member';
    }>(),
    {
        role: 'patient',
    },
);

const { t } = useI18n();

const {
    translationPrefix,
    browserSupportsPush,
    canEnableReminders,
    remindersEnabled,
    cardVariant,
    isRegistering,
    isUnregistering,
    registrationError,
    enableReminders,
    disableReminders,
} = useMedicationPushReminders(props.role);

const tKey = (key: string): string => t(`${translationPrefix}.${key}`);
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
                        {{ tKey('settingsTitle') }}
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
                            {{ tKey('settingsDescription') }}
                        </p>

                        <p
                            v-if="remindersEnabled"
                            class="text-text text-base font-medium"
                        >
                            {{ tKey('settingsEnabledStatus') }}
                        </p>

                        <p
                            v-else
                            class="text-text-muted text-base leading-relaxed"
                        >
                            {{ tKey('settingsDisabledStatus') }}
                        </p>
                    </template>
                </div>

                <div
                    v-if="cardVariant === 'enable' && !remindersEnabled"
                    class="flex flex-col gap-3 sm:flex-row"
                >
                    <Button
                        type="button"
                        size="lg"
                        class="min-h-12 w-full text-base font-semibold sm:w-auto"
                        :disabled="!canEnableReminders || isRegistering"
                        @click="enableReminders"
                    >
                        {{ tKey('enableButton') }}
                    </Button>
                </div>

                <div v-if="remindersEnabled">
                    <Button
                        type="button"
                        variant="outline"
                        size="lg"
                        class="min-h-12 w-full text-base font-semibold sm:w-auto"
                        :disabled="isUnregistering"
                        @click="disableReminders"
                    >
                        {{ tKey('disableButton') }}
                    </Button>
                </div>

                <p
                    v-if="registrationError"
                    class="text-destructive text-base leading-relaxed"
                >
                    {{ registrationError }}
                </p>

                <p
                    v-if="!browserSupportsPush"
                    class="text-text-muted text-base leading-relaxed"
                >
                    {{ tKey('missingConfig') }}
                </p>
            </div>
        </div>
    </section>
</template>
