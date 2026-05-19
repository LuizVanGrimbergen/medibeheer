<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { AuthPageContainer } from '@/Components/ui/auth-page';
import { AuthRoleSelectorWidget } from '@/Components/ui/auth-role-selector';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { PasswordRequirementsCard } from '@/Components/ui/password-requirements-card';
import { useAuthRoleOptions } from '@/lib/auth/useAuthRoleOptions';
import type { RoleKey } from '@/lib/types';
const minimumPasswordLength = 12;
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    name: '',
    email: '',
    role: '' as RoleKey | '',
    password: '',
    password_confirmation: '',
    accepted_privacy_policy: false,
    accepted_health_data_processing: false,
});

const props = defineProps<{
    selectedRole?: RoleKey | null;
    privacyPolicyVersion: string;
}>();

const { t } = useI18n();

const roles = useAuthRoleOptions();

const selectedRole = computed(() => {
    return props.selectedRole ?? null;
});

const selectedRoleLabel = computed(() => {
    if (selectedRole.value === null) {
        return null;
    }

    return t(`auth.common.roles.${selectedRole.value}`);
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

    return 'text-text-muted';
});

const bannerErrorMessage = computed(() => {
    return form.errors.role ?? '';
});

const submit = () => {
    form.email = form.email.trim().toLowerCase();
    form.clearErrors();

    if (selectedRole.value === null) {
        form.role = '';
    } else {
        form.role = selectedRole.value as RoleKey;
    }

    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head>
        <title>{{ t('auth.register.metaTitle') }}</title>
    </Head>

    <AuthPageContainer
        title-accent-key="auth.register.titleAccent"
        title-key="auth.register.title"
        :show-subtitle="false"
    >
        <AuthRoleSelectorWidget
            :roles="roles"
            :selected-role="selectedRole"
            :get-href="
                (role) => route('register', { role })
            "
        />
        <p
            v-if="bannerErrorMessage !== ''"
            class="mb-4 rounded-lg border border-danger/40 bg-danger/10 px-4 py-3 text-center text-base font-semibold text-danger"
        >
            {{ bannerErrorMessage }}
        </p>

        <div
            v-if="selectedRoleLabel"
            class="mb-4 text-center text-sm font-semibold"
            :class="selectedRoleNoticeClass"
        >
            {{ t('auth.register.roleNotice', { role: selectedRoleLabel }) }}
        </div>

        <form class="space-y-5" novalidate @submit.prevent="submit">
            <div>
                <Label for="name" class="mb-2 block text-2xl/none font-medium text-text">
                    {{ t('auth.register.nameLabel') }}
                </Label>
                <Input
                    id="name"
                    v-model="form.name"
                    name="name"
                    type="text"
                    autocomplete="name"
                    :placeholder="t('auth.register.namePlaceholder')"
                    required
                    autofocus
                    class="mt-1 h-auto w-full rounded-xl border-border bg-surface px-4 py-3 text-xl text-text placeholder:text-text-muted focus-visible:ring-focus/20"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <Label for="email" class="mb-2 block text-2xl/none font-medium text-text">
                    {{ t('auth.register.emailLabel') }}
                </Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    :placeholder="t('auth.register.emailPlaceholder')"
                    required
                    class="mt-1 h-auto w-full rounded-xl border-border bg-surface px-4 py-3 text-xl text-text placeholder:text-text-muted focus-visible:ring-focus/20"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <Label for="password" class="mb-2 block text-2xl/none font-medium text-text">
                    {{ t('auth.register.pwdLabel') }}
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
                            :placeholder="t('auth.register.pwdPlaceholder')"
                            required
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
                    {{ t('auth.register.pwdConfirmLabel') }}
                </Label>
                <div class="relative">
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        :autocomplete="showPasswordConfirmation ? 'off' : 'new-password'"
                        :placeholder="t('auth.register.pwdConfirmPlaceholder')"
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

            <div class="space-y-3">
                <p class="mb-2 text-2xl/none font-medium text-text">
                    {{ t('privacy.register.sectionTitle') }}
                </p>

                <div
                    class="flex cursor-pointer items-start gap-4 rounded-2xl border-2 border-border/70 bg-surface px-4 py-3 transition-colors hover:bg-surface-hover focus-within:ring-2 focus-within:ring-focus/25"
                    @click="form.accepted_privacy_policy = !form.accepted_privacy_policy"
                >
                    <Checkbox
                        id="register-consent-privacy"
                        :model-value="form.accepted_privacy_policy"
                        :disabled="form.processing"
                        class="mt-0.5 size-6 shrink-0"
                        @click.stop
                        @update:model-value="
                            (value) => {
                                form.accepted_privacy_policy = value === true;
                            }
                        "
                    />
                    <Label
                        for="register-consent-privacy"
                        class="min-w-0 cursor-pointer text-lg font-medium leading-relaxed text-text wrap-break-word"
                    >
                        {{ t('privacy.register.privacyPrefix') }}
                        <a
                            :href="route('legal.privacy')"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-semibold text-primary underline underline-offset-2 hover:opacity-80"
                            @click.stop
                        >
                            {{ t('privacy.register.privacyLink') }}
                        </a>
                        {{ t('privacy.register.privacySuffix', { version: props.privacyPolicyVersion }) }}
                    </Label>
                </div>
                <InputError :message="form.errors.accepted_privacy_policy" />

                <div
                    class="flex cursor-pointer items-start gap-4 rounded-2xl border-2 border-border/70 bg-surface px-4 py-3 transition-colors hover:bg-surface-hover focus-within:ring-2 focus-within:ring-focus/25"
                    @click="
                        form.accepted_health_data_processing = !form.accepted_health_data_processing
                    "
                >
                    <Checkbox
                        id="register-consent-health-data"
                        :model-value="form.accepted_health_data_processing"
                        :disabled="form.processing"
                        class="mt-0.5 size-6 shrink-0"
                        @click.stop
                        @update:model-value="
                            (value) => {
                                form.accepted_health_data_processing = value === true;
                            }
                        "
                    />
                    <Label
                        for="register-consent-health-data"
                        class="min-w-0 cursor-pointer text-lg font-medium leading-relaxed text-text wrap-break-word"
                    >
                        {{ t('privacy.register.healthDataLabel') }}
                    </Label>
                </div>
                <InputError :message="form.errors.accepted_health_data_processing" />
            </div>

            <Button
                type="submit"
                :disabled="form.processing"
                size="lg"
                class="w-full text-xl"
            >
                {{ t('auth.register.submit') }}
            </Button>
        </form>

        <p class="mt-7 text-center text-lg text-text-muted">
            {{ t('auth.register.loginPrompt') }}
            <Link
                :href="
                    selectedRole
                        ? route('login', { role: selectedRole })
                        : route('login')
                "
                class="font-semibold text-primary hover:opacity-80"
            >
                {{ t('auth.register.loginAction') }}
            </Link>
        </p>
    </AuthPageContainer>
</template>
