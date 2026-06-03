<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { computed, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { AuthPageContainer } from '@/Components/ui/auth-page';
import { AuthRoleSelectorWidget } from '@/Components/ui/auth-role-selector';
import { Button } from '@/Components/ui/button';
import { FlashSuccessBanner } from '@/Components/ui/flash-success-banner';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { useAuthRoleOptions } from '@/lib/auth/useAuthRoleOptions';
import type { PageProps, RoleKey } from '@/lib/types';

const form = useForm({
    email: '',
    password: '',
    role: '',
});

const props = defineProps<{
    canResetPassword?: boolean;
    status?: string;
    selectedRole?: RoleKey | null;
}>();

const { t } = useI18n();
const page = usePage<PageProps>();
const showPassword = ref(false);
type IntervalHandle = ReturnType<typeof globalThis.setInterval>;
const rateLimitSeconds = computed(() => page.props.flash?.rateLimitSeconds ?? null);
const remainingRateLimitSeconds = ref<number | null>(null);
const rateLimitIntervalId = ref<IntervalHandle | null>(null);

const stopRateLimitCountdown = () => {
    if (rateLimitIntervalId.value === null) {
        return;
    }

    globalThis.clearInterval(rateLimitIntervalId.value);
    rateLimitIntervalId.value = null;
};

const startRateLimitCountdown = (seconds: number | null) => {
    stopRateLimitCountdown();
    remainingRateLimitSeconds.value = seconds;

    if (seconds === null || seconds <= 0) {
        return;
    }

    rateLimitIntervalId.value = globalThis.setInterval(() => {
        if (remainingRateLimitSeconds.value === null) {
            stopRateLimitCountdown();

            return;
        }

        if (remainingRateLimitSeconds.value <= 1) {
            remainingRateLimitSeconds.value = 0;
            stopRateLimitCountdown();

            return;
        }

        remainingRateLimitSeconds.value -= 1;
    }, 1000);
};

watch(rateLimitSeconds, (seconds) => {
    startRateLimitCountdown(seconds);
}, { immediate: true });

onUnmounted(() => {
    stopRateLimitCountdown();
});

const emailErrorMessage = computed(() => {
    if (form.errors.email === undefined) {
        return undefined;
    }

    if (remainingRateLimitSeconds.value === null) {
        return form.errors.email;
    }

    return form.errors.email.replace(/\d+/, String(remainingRateLimitSeconds.value));
});

const roles = useAuthRoleOptions();

const selectedRole = computed(() => {
    return props.selectedRole ?? null;
});

const selectedRoleNoticeClass = computed(() => {
    if (selectedRole.value === 'patient') {
        return 'text-role-patient';
    }

    if (selectedRole.value === 'doctor') {
        return 'text-role-doctor';
    }

    if (selectedRole.value === 'family_member') {
        return 'text-role-family';
    }

    return 'text-text';
});

const loginRoleHighlightLine = computed((): string | null => {
    const role = selectedRole.value;

    if (role === null) {
        return null;
    }

    return t(`auth.login.roleNotices.${role}`);
});

const canSubmit = computed(() => {
    if (selectedRole.value === null) {
        return false;
    }

    if (form.email.trim() === '') {
        return false;
    }

    if (form.password === '') {
        return false;
    }

    return true;
});

const submit = () => {
    if (!canSubmit.value) {
        return;
    }

    form.email = form.email.trim().toLowerCase();
    form.clearErrors();
    form.role = selectedRole.value as RoleKey;

    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head>
        <title>{{ t('auth.login.metaTitle') }}</title>
    </Head>

    <AuthPageContainer
        title-key="auth.login.heroTitlePrefix"
        subtitle-key="auth.common.roleSelectorHint"
        subtitle-tone="body"
        :show-subtitle="selectedRole === null"
        append-app-name
    >
        <AuthRoleSelectorWidget
            :roles="roles"
            :selected-role="selectedRole"
            :get-href="
                (role) => route('login', { role })
            "
        />

        <template v-if="selectedRole !== null">
            <div
                v-if="loginRoleHighlightLine !== null"
                class="mb-4 text-center text-sm font-semibold"
                :class="selectedRoleNoticeClass"
            >
                {{ loginRoleHighlightLine }}
            </div>

            <FlashSuccessBanner
                class="mb-4"
                :message="props.status ?? null"
            />

            <form class="space-y-5" novalidate @submit.prevent="submit">
            <div>
                <Label for="email" class="mb-2 block text-2xl/none font-medium text-text">
                    {{ t('auth.login.emailLabel') }}
                    <span class="text-danger">*</span>
                </Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    :placeholder="t('auth.login.emailPlaceholder')"
                    required
                    autofocus
                    class="mt-1 h-auto w-full rounded-xl border-border bg-surface px-4 py-3 text-xl text-text placeholder:text-text-muted focus-visible:ring-focus/20"
                />
                <InputError class="mt-2" :message="emailErrorMessage" />
            </div>

            <div>
                <Label for="password" class="mb-2 block text-2xl/none font-medium text-text">
                    {{ t('auth.login.pwdLabel') }}
                    <span class="text-danger">*</span>
                </Label>
                <div class="relative">
                    <Input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        :autocomplete="showPassword ? 'off' : 'current-password'"
                        :placeholder="t('auth.login.pwdPlaceholder')"
                        required
                        class="mt-1 h-auto w-full rounded-xl border-border bg-surface px-4 py-3 pr-12 text-xl text-text placeholder:text-text-muted focus-visible:ring-focus/20"
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
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <Button
                type="submit"
                :disabled="form.processing || !canSubmit"
                size="lg"
                class="w-full text-xl"
            >
                {{ t('auth.login.submit') }}
            </Button>
            </form>

            <p class="mt-7 text-center text-lg text-text-muted">
                {{ t('auth.login.registerPrompt') }}
                <Link
                    :href="route('register', { role: selectedRole })"
                    class="font-semibold text-primary hover:opacity-80"
                >
                    {{ t('auth.login.registerAction') }}
                </Link>
            </p>

            <p v-if="canResetPassword" class="mt-2 text-center text-sm text-text-muted">
                <Link
                    :href="route('password.request')"
                    class="underline underline-offset-2 hover:text-text"
                >
                    {{ t('auth.login.forgotPassword') }}
                </Link>
            </p>
        </template>
    </AuthPageContainer>
</template>
