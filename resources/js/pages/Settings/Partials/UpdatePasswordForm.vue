<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { PasswordRequirementsCard } from '@/Components/ui/password-requirements-card';
import { patientFormWizardFooterPrimaryButtonClass } from '@/lib/patient/patientShellDialogLayout';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);
const minimumPasswordLength = 12;
const { t } = useI18n();

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }

            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-text text-lg font-medium">
                {{ t('profile.password.title') }}
            </h2>

            <p class="text-text-muted mt-1 text-sm">
                {{ t('profile.password.description') }}
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <Label
                    for="current_password"
                    class="text-text mb-2 block text-2xl/none font-medium"
                >
                    {{ t('profile.password.currentPassword') }}
                </Label>

                <Input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="border-border bg-surface text-text placeholder:text-text-muted focus-visible:ring-focus/20 mt-1 h-auto w-full rounded-xl px-4 py-3 text-xl"
                    autocomplete="current-password"
                />

                <InputError
                    :message="form.errors.current_password"
                    class="mt-2"
                />
            </div>

            <div>
                <Label
                    for="password"
                    class="text-text mb-2 block text-2xl/none font-medium"
                >
                    {{ t('profile.password.newPassword') }}
                </Label>

                <div
                    class="border-border bg-surface focus-within:ring-focus/25 mt-1 overflow-hidden rounded-xl border focus-within:ring-2"
                >
                    <Input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="text-text placeholder:text-text-muted h-auto w-full rounded-none border-0 bg-transparent px-4 py-3 text-xl shadow-none focus-visible:ring-0"
                        autocomplete="new-password"
                    />

                    <PasswordRequirementsCard
                        integrated
                        :password="form.password"
                        :minimum-length="minimumPasswordLength"
                    />
                </div>

                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <Label
                    for="password_confirmation"
                    class="text-text mb-2 block text-2xl/none font-medium"
                >
                    {{ t('profile.password.confirmPassword') }}
                </Label>

                <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="border-border bg-surface text-text placeholder:text-text-muted focus-visible:ring-focus/20 mt-1 h-auto w-full rounded-xl px-4 py-3 text-xl"
                    autocomplete="new-password"
                />

                <InputError
                    :message="form.errors.password_confirmation"
                    class="mt-2"
                />
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <Button
                    type="submit"
                    size="lg"
                    :class="patientFormWizardFooterPrimaryButtonClass"
                    :disabled="form.processing"
                >
                    {{ t('profile.password.save') }}
                </Button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-text-muted text-sm"
                    >
                        {{ t('profile.password.saved') }}
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
