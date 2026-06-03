<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { AuthPageContainer } from '@/Components/ui/auth-page';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';

defineProps<{
    backUrl: string;
}>();

const form = useForm({
    password: '',
});

const { t } = useI18n();

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head>
        <title>{{ t('auth.confirmPwd.metaTitle') }}</title>
    </Head>

    <AuthPageContainer
        title-key="auth.confirmPwd.title"
        subtitle-key="auth.confirmPwd.intro"
    >
        <template #top>
            <div class="mb-6">
                <Link
                    :href="backUrl"
                    class="text-text-muted hover:text-text focus:ring-focus/20 inline-flex items-center gap-2 rounded-lg px-2 py-1 text-sm font-semibold focus:ring-2 focus:outline-none"
                >
                    <svg
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        class="h-5 w-5"
                        aria-hidden="true"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M11.78 15.53a.75.75 0 0 1-1.06 0l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 1 1 1.06 1.06L7.31 9.72H15a.75.75 0 0 1 0 1.5H7.31l4.47 4.47a.75.75 0 0 1 0 1.06Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    {{ t('auth.confirmPwd.back', 'Terug') }}
                </Link>
            </div>
        </template>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <Label
                    for="password"
                    class="text-text mb-2 block text-2xl/none font-medium"
                >
                    {{ t('auth.confirmPwd.pwdLabel') }}
                </Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    required
                    autofocus
                    class="border-border bg-surface text-text placeholder:text-text-muted focus-visible:ring-focus/20 mt-1 h-auto w-full rounded-xl px-4 py-3 text-xl"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <Button
                type="submit"
                :disabled="form.processing"
                size="lg"
                class="w-full text-xl"
            >
                {{ t('auth.confirmPwd.submit') }}
            </Button>
        </form>
    </AuthPageContainer>
</template>
