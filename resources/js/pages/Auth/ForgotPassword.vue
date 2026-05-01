<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { AuthPageContainer } from '@/Components/ui/auth-page';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const { t } = useI18n();

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head>
        <title>{{ t('auth.forgotPwd.metaTitle') }}</title>
    </Head>

    <AuthPageContainer
        title-key="auth.forgotPwd.title"
        subtitle-key="auth.forgotPwd.intro"
    >
        <div v-if="props.status" class="mb-4 rounded-lg bg-success/10 px-4 py-3 text-sm font-medium text-success">
            {{ props.status }}
        </div>

        <form class="space-y-5" novalidate @submit.prevent="submit">
            <div>
                <Label for="email" class="mb-2 block text-2xl/none font-medium text-text">{{ t('auth.forgotPwd.emailLabel') }}</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    :placeholder="t('auth.forgotPwd.emailPlaceholder')"
                    required
                    autofocus
                    autocomplete="email"
                    class="mt-1 h-auto w-full rounded-xl border-border bg-surface px-4 py-3 text-xl text-text placeholder:text-text-muted focus-visible:ring-focus/20"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <Button
                type="submit"
                :disabled="form.processing"
                size="lg"
                class="w-full text-xl"
            >
                {{ t('auth.forgotPwd.submit') }}
            </Button>
        </form>
    </AuthPageContainer>
</template>
