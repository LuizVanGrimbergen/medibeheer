<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
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
            <h2 class="text-lg font-medium text-text">
                {{ t('profile.information.title') }}
            </h2>

            <p class="mt-1 text-sm text-text-muted">
                {{ t('profile.information.description') }}
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('settings.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <Label for="name" class="mb-2 block text-2xl/none font-medium text-text">
                    {{ t('profile.information.nameLabel') }}
                </Label>

                <Input
                    id="name"
                    type="text"
                    class="mt-1 h-auto w-full rounded-xl border-border bg-surface px-4 py-3 text-xl text-text placeholder:text-text-muted focus-visible:ring-focus/20"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <Label for="email" class="mb-2 block text-2xl/none font-medium text-text">
                    {{ t('profile.information.emailLabel') }}
                </Label>

                <Input
                    id="email"
                    type="email"
                    class="mt-1 h-auto w-full rounded-xl border-border bg-surface px-4 py-3 text-xl text-text placeholder:text-text-muted focus-visible:ring-focus/20"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <Label for="current_password" class="mb-2 block text-2xl/none font-medium text-text">
                    {{ t('profile.password.currentPassword') }}
                </Label>

                <Input
                    id="current_password"
                    type="password"
                    class="mt-1 h-auto w-full rounded-xl border-border bg-surface px-4 py-3 text-xl text-text placeholder:text-text-muted focus-visible:ring-focus/20"
                    v-model="form.current_password"
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.current_password" />
            </div>

            <div
                v-if="props.mustVerifyEmail && user.email_verified_at === null"
                class="space-y-1 text-sm text-text-muted"
            >
                <p>{{ t('profile.information.emailUnverified') }}</p>
                <Link
                    :href="route('verification.send')"
                    method="post"
                    as="button"
                    type="button"
                    class="rounded-md font-medium text-primary hover:opacity-80"
                >
                    {{ t('profile.information.resendVerification') }}
                </Link>
                <p
                    v-if="props.status === 'verification-link-sent'"
                    class="font-medium text-success"
                >
                    {{ t('profile.information.verificationSent') }}
                </p>
            </div>

            <div class="flex items-center gap-4">
                <Button
                    type="submit"
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
                        class="text-sm text-text-muted"
                    >
                        {{ t('profile.information.saved') }}
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
