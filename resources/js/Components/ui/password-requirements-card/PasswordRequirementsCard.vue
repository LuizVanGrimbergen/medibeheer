<script setup lang="ts">
import { Circle, CircleCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = withDefaults(
    defineProps<{
        password?: string;
        minimumLength: number;
    }>(),
    {
        password: '',
    },
);

const { t } = useI18n();

const password = computed(() => props.password ?? '');

const remainingPasswordCharacters = computed(() => {
    const currentLength = password.value.length;

    if (currentLength >= props.minimumLength) {
        return 0;
    }

    return props.minimumLength - currentLength;
});

const hasMinimumPasswordLength = computed(() => {
    return remainingPasswordCharacters.value === 0;
});

const hasUppercaseLetter = computed(() => {
    return /[A-Z]/.test(password.value);
});

const hasLowercaseLetter = computed(() => {
    return /[a-z]/.test(password.value);
});

const hasDigit = computed(() => {
    return /\d/.test(password.value);
});

const hasSpecialCharacter = computed(() => {
    return /[^A-Za-z0-9]/.test(password.value);
});
</script>

<template>
    <div class="-mt-1 rounded-xl border border-border bg-surface px-4 py-3">
        <p class="text-base font-semibold text-text">
            {{ t('auth.register.pwdHelperTitle') }}
        </p>
        <p
            class="mt-2 rounded-lg px-3 py-2 text-base font-semibold"
            :class="
                hasMinimumPasswordLength
                    ? 'bg-success/10 text-success'
                    : 'bg-warning/40 text-warning-text'
            "
        >
            <span v-if="hasMinimumPasswordLength">
                {{
                    t('auth.register.pwdStrengthMet', {
                        count: minimumLength,
                    })
                }}
            </span>
            <span v-else>
                {{
                    t('auth.register.pwdStrengthRemaining', {
                        count: remainingPasswordCharacters,
                    })
                }}
            </span>
        </p>
        <ul class="mt-2 space-y-1 text-base leading-6 text-text">
            <li class="flex items-center gap-2" :class="hasMinimumPasswordLength ? 'font-semibold text-success' : ''">
                <component
                    :is="hasMinimumPasswordLength ? CircleCheck : Circle"
                    :size="16"
                    :class="hasMinimumPasswordLength ? 'text-success' : 'text-text-muted'"
                />
                <span>
                    {{
                        t('auth.register.pwdRuleMinLength', {
                            count: minimumLength,
                        })
                    }}
                </span>
            </li>
            <li class="flex items-center gap-2" :class="hasUppercaseLetter ? 'font-semibold text-success' : ''">
                <component
                    :is="hasUppercaseLetter ? CircleCheck : Circle"
                    :size="16"
                    :class="hasUppercaseLetter ? 'text-success' : 'text-text-muted'"
                />
                <span>{{ t('auth.register.pwdRuleUppercase') }}</span>
            </li>
            <li class="flex items-center gap-2" :class="hasLowercaseLetter ? 'font-semibold text-success' : ''">
                <component
                    :is="hasLowercaseLetter ? CircleCheck : Circle"
                    :size="16"
                    :class="hasLowercaseLetter ? 'text-success' : 'text-text-muted'"
                />
                <span>{{ t('auth.register.pwdRuleLowercase') }}</span>
            </li>
            <li class="flex items-center gap-2" :class="hasDigit ? 'font-semibold text-success' : ''">
                <component
                    :is="hasDigit ? CircleCheck : Circle"
                    :size="16"
                    :class="hasDigit ? 'text-success' : 'text-text-muted'"
                />
                <span>{{ t('auth.register.pwdRuleDigit') }}</span>
            </li>
            <li class="flex items-center gap-2" :class="hasSpecialCharacter ? 'font-semibold text-success' : ''">
                <component
                    :is="hasSpecialCharacter ? CircleCheck : Circle"
                    :size="16"
                    :class="hasSpecialCharacter ? 'text-success' : 'text-text-muted'"
                />
                <span>{{ t('auth.register.pwdRuleSpecial') }}</span>
            </li>
        </ul>
        <p class="mt-2 text-sm text-text-muted">
            {{ t('auth.register.pwdServerChecks') }}
        </p>
    </div>
</template>
