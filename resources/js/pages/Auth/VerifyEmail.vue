<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import SeoHead from '@/Components/Seo/SeoHead.vue';
import { SessionActionSuccessScreen } from '@/Components/ui/action-success-screen';
import { AuthPageContainer } from '@/Components/ui/auth-page';
import { Button } from '@/Components/ui/button';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});
const { t } = useI18n();

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);

const verificationLinkSentMessage = computed(() =>
    verificationLinkSent.value ? t('auth.verifyEmail.linkSent') : null,
);
</script>

<template>
    <SeoHead
        :title="t('auth.verifyEmail.metaTitle')"
        :description="t('auth.verifyEmail.metaDescription')"
        no-index
    />

    <AuthPageContainer
        title-key="auth.verifyEmail.title"
        subtitle-key="auth.verifyEmail.intro"
    >
        <SessionActionSuccessScreen :message="verificationLinkSentMessage" />

        <form class="space-y-3" @submit.prevent="submit">
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="bg-primary focus:ring-focus/20 inline-flex h-11 w-full cursor-pointer items-center justify-center rounded-xl px-8 text-xl font-semibold text-white transition hover:opacity-90 focus:ring-2 focus:outline-none"
            >
                {{ t('auth.verifyEmail.logoutAction') }}
            </Link>

            <Button
                type="submit"
                :disabled="form.processing"
                variant="outline"
                size="lg"
                class="w-full"
            >
                {{ t('auth.verifyEmail.resendAction') }}
            </Button>
        </form>
    </AuthPageContainer>
</template>
