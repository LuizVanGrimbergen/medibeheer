<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { AuthPageContainer } from '@/Components/ui/auth-page';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { PasswordRequirementsCard } from '@/Components/ui/password-requirements-card';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});
const minimumPasswordLength = 12;
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const { t } = useI18n();

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head>
        <title>{{ t('auth.resetPwd.metaTitle') }}</title>
    </Head>

    <AuthPageContainer
        title-key="auth.resetPwd.title"
        subtitle-key="auth.resetPwd.subtitle"
    >
        <form class="space-y-5" novalidate @submit.prevent="submit">
            <div>
                <Label for="password" class="mb-2 block text-2xl/none font-medium text-text">
                    {{ t('auth.resetPwd.pwdLabel') }}
                </Label>
                <div
                    class="mt-1 overflow-hidden rounded-xl border border-border bg-surface focus-within:ring-2 focus-within:ring-focus/25"
                >
                    <div class="relative">
                        <Input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            :autocomplete="showPassword ? 'off' : 'new-password'"
                            required
                            autofocus
                            class="h-auto w-full rounded-none border-0 bg-transparent px-4 py-3 pr-12 text-xl text-text shadow-none placeholder:text-text-muted focus-visible:ring-0"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted transition hover:text-text"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'"
                            @click="showPassword = !showPassword"
                        >
                            <EyeOff v-if="showPassword" :size="20" />
                            <Eye v-else :size="20" />
                        </button>
                    </div>

                    <PasswordRequirementsCard
                        integrated
                        :password="form.password"
                        :minimum-length="minimumPasswordLength"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <Label for="password_confirmation" class="mb-2 block text-2xl/none font-medium text-text">
                    {{ t('auth.resetPwd.pwdConfirmLabel') }}
                </Label>
                <div class="relative">
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        :autocomplete="showPasswordConfirmation ? 'off' : 'new-password'"
                        required
                        class="mt-1 h-auto w-full rounded-xl border-border bg-surface px-4 py-3 pr-12 text-xl text-text placeholder:text-text-muted focus-visible:ring-focus/20"
                    />
                    <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted transition hover:text-text"
                        :aria-label="showPasswordConfirmation ? 'Hide password confirmation' : 'Show password confirmation'"
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                    >
                        <EyeOff v-if="showPasswordConfirmation" :size="20" />
                        <Eye v-else :size="20" />
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                :disabled="form.processing"
                size="lg"
                class="w-full text-xl"
            >
                {{ t('auth.resetPwd.submit') }}
            </Button>

            <input v-model="form.email" type="hidden" />
            <InputError class="mt-2" :message="form.errors.email" />
        </form>
    </AuthPageContainer>
</template>
