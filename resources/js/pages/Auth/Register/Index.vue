<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SeoHead from '@/Components/Seo/SeoHead.vue';
import { AuthPageContainer } from '@/Components/ui/auth-page';
import { AuthRoleSelectorWidget } from '@/Components/ui/auth-role-selector';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { InputError } from '@/Components/ui/input-error';
import { Label } from '@/Components/ui/label';
import { PasswordRequirementsCard } from '@/Components/ui/password-requirements-card';
import { useAuthRoleOptions } from '@/lib/auth/useAuthRoleOptions';
import { authRouteWithEncryptedRole } from '@/lib/auth/useAuthRoleRoute';
import type { RoleKey, RoleTokens } from '@/lib/types';
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
    accepted_terms_of_service: false,
});

const props = defineProps<{
    selectedRole?: RoleKey | null;
    roleTokens: RoleTokens;
    privacyPolicyVersion: string;
    termsVersion: string;
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

    return 'text-text';
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
    <SeoHead
        :title="t('auth.register.metaTitle')"
        :description="t('auth.register.metaDescription')"
    />

    <AuthPageContainer
        title-accent-key="auth.register.titleAccent"
        title-key="auth.register.title"
        subtitle-key="auth.common.roleSelectorHint"
        subtitle-tone="body"
        :show-subtitle="selectedRole === null"
    >
        <AuthRoleSelectorWidget
            :roles="roles"
            :selected-role="selectedRole"
            :get-href="
                (role) =>
                    authRouteWithEncryptedRole(
                        'register',
                        props.roleTokens,
                        role,
                    )
            "
        />
        <p
            v-if="bannerErrorMessage !== ''"
            class="border-danger/40 bg-danger/10 text-danger mb-4 rounded-lg border px-4 py-3 text-center text-base font-semibold"
        >
            {{ bannerErrorMessage }}
        </p>

        <template v-if="selectedRole !== null">
            <div
                class="mb-4 text-center text-sm font-semibold"
                :class="selectedRoleNoticeClass"
            >
                {{ t('auth.register.roleNotice', { role: selectedRoleLabel }) }}
            </div>

            <form class="space-y-5" novalidate @submit.prevent="submit">
                <div>
                    <Label
                        for="name"
                        class="text-text mb-2 block text-2xl/none font-medium"
                    >
                        {{ t('auth.register.nameLabel') }}
                        <span class="text-danger">*</span>
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
                        class="border-border bg-surface text-text placeholder:text-text-muted focus-visible:ring-focus/20 mt-1 h-auto w-full rounded-xl px-4 py-3 text-xl"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <Label
                        for="email"
                        class="text-text mb-2 block text-2xl/none font-medium"
                    >
                        {{ t('auth.register.emailLabel') }}
                        <span class="text-danger">*</span>
                    </Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        :placeholder="t('auth.register.emailPlaceholder')"
                        required
                        class="border-border bg-surface text-text placeholder:text-text-muted focus-visible:ring-focus/20 mt-1 h-auto w-full rounded-xl px-4 py-3 text-xl"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <Label
                        for="password"
                        class="text-text mb-2 block text-2xl/none font-medium"
                    >
                        {{ t('auth.register.pwdLabel') }}
                        <span class="text-danger">*</span>
                    </Label>
                    <div
                        class="border-border bg-surface focus-within:ring-focus/25 mt-1 overflow-hidden rounded-xl border focus-within:ring-2"
                    >
                        <div class="relative">
                            <Input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                :autocomplete="
                                    showPassword ? 'off' : 'new-password'
                                "
                                :placeholder="t('auth.register.pwdPlaceholder')"
                                required
                                class="text-text placeholder:text-text-muted h-auto w-full rounded-none border-0 bg-transparent px-4 py-3 pr-12 text-xl shadow-none focus-visible:ring-0"
                            />
                            <button
                                type="button"
                                class="text-text-muted hover:text-text absolute top-1/2 right-3 -translate-y-1/2 transition"
                                :aria-label="
                                    showPassword
                                        ? 'Hide password'
                                        : 'Show password'
                                "
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
                    <Label
                        for="password_confirmation"
                        class="text-text mb-2 block text-2xl/none font-medium"
                    >
                        {{ t('auth.register.pwdConfirmLabel') }}
                        <span class="text-danger">*</span>
                    </Label>
                    <div class="relative">
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="
                                showPasswordConfirmation ? 'text' : 'password'
                            "
                            :autocomplete="
                                showPasswordConfirmation
                                    ? 'off'
                                    : 'new-password'
                            "
                            :placeholder="
                                t('auth.register.pwdConfirmPlaceholder')
                            "
                            required
                            class="border-border bg-surface text-text placeholder:text-text-muted focus-visible:ring-focus/20 mt-1 h-auto w-full rounded-xl px-4 py-3 pr-12 text-xl"
                        />
                        <button
                            type="button"
                            class="text-text-muted hover:text-text absolute top-1/2 right-3 -translate-y-1/2 transition"
                            :aria-label="
                                showPasswordConfirmation
                                    ? 'Hide password confirmation'
                                    : 'Show password confirmation'
                            "
                            @click="
                                showPasswordConfirmation =
                                    !showPasswordConfirmation
                            "
                        >
                            <EyeOff
                                v-if="showPasswordConfirmation"
                                :size="20"
                            />
                            <Eye v-else :size="20" />
                        </button>
                    </div>
                    <InputError
                        class="mt-2"
                        :message="form.errors.password_confirmation"
                    />
                </div>

                <fieldset class="space-y-3 border-0 p-0">
                    <legend
                        class="text-text mb-2 block w-full text-2xl/none font-medium"
                    >
                        {{ t('privacy.register.sectionTitle') }}
                    </legend>

                    <div
                        class="border-border/70 bg-surface hover:bg-surface-hover focus-within:ring-focus/25 flex cursor-pointer items-start gap-4 rounded-2xl border-2 px-4 py-3 transition-colors focus-within:ring-2"
                        @click="
                            form.accepted_privacy_policy =
                                !form.accepted_privacy_policy
                        "
                    >
                        <Checkbox
                            id="register-consent-privacy"
                            :model-value="form.accepted_privacy_policy"
                            :disabled="form.processing"
                            required
                            class="mt-0.5 size-6 shrink-0"
                            @click.stop
                            @update:model-value="
                                (value) => {
                                    form.accepted_privacy_policy =
                                        value === true;
                                }
                            "
                        />
                        <Label
                            for="register-consent-privacy"
                            class="text-text min-w-0 cursor-pointer text-lg leading-relaxed font-medium wrap-break-word"
                        >
                            <span class="text-danger">*</span>
                            {{ ' ' }}
                            {{ t('privacy.register.privacyPrefix') }}
                            <a
                                :href="route('legal.privacy')"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-primary font-semibold underline underline-offset-2 hover:opacity-80"
                                @click.stop
                            >
                                {{ t('privacy.register.privacyLink') }}
                            </a>
                            {{
                                t('privacy.register.privacySuffix', {
                                    version: props.privacyPolicyVersion,
                                })
                            }}
                            <a
                                :href="route('legal.cookies')"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-primary font-semibold underline underline-offset-2 hover:opacity-80"
                                @click.stop
                            >
                                {{ t('privacy.register.cookiesLink') }}
                            </a>
                            {{ t('privacy.register.privacySuffixEnd') }}
                        </Label>
                    </div>
                    <InputError
                        :message="form.errors.accepted_privacy_policy"
                    />

                    <div
                        class="border-border/70 bg-surface hover:bg-surface-hover focus-within:ring-focus/25 flex cursor-pointer items-start gap-4 rounded-2xl border-2 px-4 py-3 transition-colors focus-within:ring-2"
                        @click="
                            form.accepted_terms_of_service =
                                !form.accepted_terms_of_service
                        "
                    >
                        <Checkbox
                            id="register-consent-terms"
                            :model-value="form.accepted_terms_of_service"
                            :disabled="form.processing"
                            required
                            class="mt-0.5 size-6 shrink-0"
                            @click.stop
                            @update:model-value="
                                (value) => {
                                    form.accepted_terms_of_service =
                                        value === true;
                                }
                            "
                        />
                        <Label
                            for="register-consent-terms"
                            class="text-text min-w-0 cursor-pointer text-lg leading-relaxed font-medium wrap-break-word"
                        >
                            <span class="text-danger">*</span>
                            {{ ' ' }}
                            {{ t('legal.register.termsPrefix') }}
                            <a
                                :href="route('legal.terms')"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-primary font-semibold underline underline-offset-2 hover:opacity-80"
                                @click.stop
                            >
                                {{ t('legal.register.termsLink') }}
                            </a>
                            {{
                                t('legal.register.termsSuffix', {
                                    version: props.termsVersion,
                                })
                            }}
                        </Label>
                    </div>
                    <InputError
                        :message="form.errors.accepted_terms_of_service"
                    />

                    <div
                        class="border-border/70 bg-surface hover:bg-surface-hover focus-within:ring-focus/25 flex cursor-pointer items-start gap-4 rounded-2xl border-2 px-4 py-3 transition-colors focus-within:ring-2"
                        @click="
                            form.accepted_health_data_processing =
                                !form.accepted_health_data_processing
                        "
                    >
                        <Checkbox
                            id="register-consent-health-data"
                            :model-value="form.accepted_health_data_processing"
                            :disabled="form.processing"
                            required
                            class="mt-0.5 size-6 shrink-0"
                            @click.stop
                            @update:model-value="
                                (value) => {
                                    form.accepted_health_data_processing =
                                        value === true;
                                }
                            "
                        />
                        <Label
                            for="register-consent-health-data"
                            class="text-text min-w-0 cursor-pointer text-lg leading-relaxed font-medium wrap-break-word"
                        >
                            <span class="text-danger">*</span>
                            {{ ' ' }}
                            {{ t('privacy.register.healthDataLabel') }}
                        </Label>
                    </div>
                    <InputError
                        :message="form.errors.accepted_health_data_processing"
                    />
                </fieldset>

                <Button
                    type="submit"
                    :disabled="form.processing"
                    size="lg"
                    class="w-full text-xl"
                >
                    {{ t('auth.register.submit') }}
                </Button>
            </form>

            <p class="text-text-muted mt-7 text-center text-lg">
                {{ t('auth.register.loginPrompt') }}
                <Link
                    :href="
                        authRouteWithEncryptedRole(
                            'login',
                            props.roleTokens,
                            selectedRole,
                        )
                    "
                    class="text-primary font-semibold hover:opacity-80"
                >
                    {{ t('auth.register.loginAction') }}
                </Link>
            </p>
        </template>
    </AuthPageContainer>
</template>
