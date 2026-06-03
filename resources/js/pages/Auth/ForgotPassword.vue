<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import SeoHead from '@/Components/Seo/SeoHead.vue';
import { AuthPageContainer } from '@/Components/ui/auth-page';
import { Button } from '@/Components/ui/button';
import { FlashSuccessBanner } from '@/Components/ui/flash-success-banner';
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
    <SeoHead
        :title="t('auth.forgotPwd.metaTitle')"
        :description="t('auth.forgotPwd.metaDescription')"
    />

    <AuthPageContainer
        title-key="auth.forgotPwd.title"
        subtitle-key="auth.forgotPwd.intro"
    >
        <FlashSuccessBanner class="mb-4" :message="props.status ?? null" />

        <form class="space-y-5" novalidate @submit.prevent="submit">
            <div>
                <Label
                    for="email"
                    class="text-text mb-2 block text-2xl/none font-medium"
                    >{{ t('auth.forgotPwd.emailLabel') }}</Label
                >
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    :placeholder="t('auth.forgotPwd.emailPlaceholder')"
                    required
                    autofocus
                    autocomplete="email"
                    class="border-border bg-surface text-text placeholder:text-text-muted focus-visible:ring-focus/20 mt-1 h-auto w-full rounded-xl px-4 py-3 text-xl"
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
