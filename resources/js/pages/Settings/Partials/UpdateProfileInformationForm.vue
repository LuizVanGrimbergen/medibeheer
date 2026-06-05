<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { patientFormWizardFooterPrimaryButtonClass } from '@/lib/patient/patientShellDialogLayout';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import type { PageProps } from '@/lib/types';

const props = defineProps<{
    mustVerifyEmail: boolean;
    status?: string;
}>();

const user = usePage<PageProps>().props.auth.user!;
const { t } = useI18n();

const form = useForm({
    name: user.name,
    email: user.email ?? '',
    current_password: '',
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-text text-lg font-medium">
                {{ t('profile.information.title') }}
            </h2>

            <p class="text-text-muted mt-1 text-sm">
                {{ t('profile.information.description') }}
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('settings.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <Label
                    for="name"
                    class="text-text mb-2 block text-2xl/none font-medium"
                >
                    {{ t('profile.information.nameLabel') }}
                </Label>

                <Input
                    id="name"
                    type="text"
                    class="border-border bg-surface text-text placeholder:text-text-muted focus-visible:ring-focus/20 mt-1 h-auto w-full rounded-xl px-4 py-3 text-xl"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <Label
                    for="email"
                    class="text-text mb-2 block text-2xl/none font-medium"
                >
                    {{ t('profile.information.emailLabel') }}
                </Label>

                <Input
                    id="email"
                    type="email"
                    class="border-border bg-surface text-text placeholder:text-text-muted focus-visible:ring-focus/20 mt-1 h-auto w-full rounded-xl px-4 py-3 text-xl"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <Label
                    for="current_password"
                    class="text-text mb-2 block text-2xl/none font-medium"
                >
                    {{ t('profile.password.currentPassword') }}
                </Label>

                <Input
                    id="current_password"
                    type="password"
                    class="border-border bg-surface text-text placeholder:text-text-muted focus-visible:ring-focus/20 mt-1 h-auto w-full rounded-xl px-4 py-3 text-xl"
                    v-model="form.current_password"
                    autocomplete="current-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.current_password"
                />
            </div>

            <div
                v-if="props.mustVerifyEmail && user.email_verified_at === null"
                class="text-text-muted space-y-1 text-sm"
            >
                <p>{{ t('profile.information.emailUnverified') }}</p>
                <Link
                    :href="route('verification.send')"
                    method="post"
                    as="button"
                    type="button"
                    class="text-primary rounded-md font-medium hover:opacity-80"
                >
                    {{ t('profile.information.resendVerification') }}
                </Link>
                <p
                    v-if="props.status === 'verification-link-sent'"
                    class="text-success font-medium"
                >
                    {{ t('profile.information.verificationSent') }}
                </p>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <Button
                    type="submit"
                    size="lg"
                    :class="patientFormWizardFooterPrimaryButtonClass"
                    :disabled="form.processing"
                >
                    {{ t('profile.information.save') }}
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
                        {{ t('profile.information.saved') }}
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
