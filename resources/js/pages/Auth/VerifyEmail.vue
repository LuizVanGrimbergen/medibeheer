<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { AuthPageContainer } from '@/Components/ui/auth-page';
import { Button } from '@/Components/ui/button';
import { FlashSuccessBanner } from '@/Components/ui/flash-success-banner';

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
    <Head>
        <title>{{ t('auth.verifyEmail.metaTitle') }}</title>
    </Head>

    <AuthPageContainer
        title-key="auth.verifyEmail.title"
        subtitle-key="auth.verifyEmail.intro"
    >
        <FlashSuccessBanner
            class="mb-4"
            :message="verificationLinkSentMessage"
        />

        <form class="space-y-3" @submit.prevent="submit">
            <Button
                type="submit"
                :disabled="form.processing"
                size="lg"
                class="w-full text-xl"
            >
                {{ t('auth.verifyEmail.resendAction') }}
            </Button>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="block w-full rounded-xl border border-border bg-surface px-4 py-3 text-center text-base font-semibold text-text transition hover:bg-surface-hover"
            >
                {{ t('auth.verifyEmail.logoutAction') }}
            </Link>
        </form>
    </AuthPageContainer>
</template>
