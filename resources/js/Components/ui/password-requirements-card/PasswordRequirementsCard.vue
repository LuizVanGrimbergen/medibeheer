<script setup lang="ts">
import { Circle, CircleCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Progress } from '@/Components/ui/progress';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        password?: string;
        minimumLength: number;
        integrated?: boolean;
    }>(),
    {
        password: '',
        integrated: false,
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

const hasMinimumPasswordLength = computed(() => remainingPasswordCharacters.value === 0);

const hasUppercaseLetter = computed(() => /[A-Z]/.test(password.value));

const hasLowercaseLetter = computed(() => /[a-z]/.test(password.value));

const hasDigit = computed(() => /\d/.test(password.value));

const hasSpecialCharacter = computed(() => /[^A-Za-z0-9]/.test(password.value));

const rules = computed(() => {
    return [
        {
            met: hasMinimumPasswordLength.value,
            label: t('auth.register.pwdRuleMinLength', {
                count: props.minimumLength,
            }),
        },
        {
            met: hasUppercaseLetter.value,
            label: t('auth.register.pwdRuleUppercase'),
        },
        {
            met: hasLowercaseLetter.value,
            label: t('auth.register.pwdRuleLowercase'),
        },
        {
            met: hasDigit.value,
            label: t('auth.register.pwdRuleDigit'),
        },
        {
            met: hasSpecialCharacter.value,
            label: t('auth.register.pwdRuleSpecial'),
        },
    ];
});

const fulfilledRulesCount = computed(() => {
    return rules.value.filter((rule) => rule.met).length;
});

const totalRulesCount = computed(() => rules.value.length);

const allRulesMet = computed(() => fulfilledRulesCount.value === totalRulesCount.value);

const hasStartedTyping = computed(() => password.value.length > 0);

const progressPercent = computed(() => {
    if (totalRulesCount.value === 0) {
        return 0;
    }

    return Math.round((fulfilledRulesCount.value / totalRulesCount.value) * 100);
});

const statusVariant = computed((): 'allMet' | 'start' | 'remaining' | 'continue' => {
    if (allRulesMet.value) {
        return 'allMet';
    }

    if (!hasStartedTyping.value) {
        return 'start';
    }

    if (!hasMinimumPasswordLength.value) {
        return 'remaining';
    }

    return 'continue';
});
</script>

<template>
    <section
        :class="
            cn(
                props.integrated
                    ? 'border-t-2 border-border/70 bg-surface-2/60 px-3 py-3'
                    : 'mt-3 rounded-2xl border-2 border-border/70 bg-surface px-4 py-4 sm:px-5 sm:py-5',
            )
        "
        :aria-label="t('auth.register.pwdHelperTitle')"
    >
        <p
            class="rounded-xl border px-3 py-2.5 text-base font-semibold leading-snug sm:px-4 sm:py-3 sm:text-lg"
            :class="
                cn(
                    allRulesMet
                        ? 'border-success/40 bg-success/10 text-success'
                        : hasStartedTyping
                          ? 'border-primary/30 bg-primary/10 text-primary'
                          : 'border-border bg-surface text-text',
                )
            "
            aria-live="polite"
        >
            <span v-if="statusVariant === 'allMet'">
                {{ t('auth.register.pwdStrengthAllMet', { count: minimumLength }) }}
            </span>
            <span v-else-if="statusVariant === 'start'">
                {{ t('auth.register.pwdStrengthStart') }}
            </span>
            <span v-else-if="statusVariant === 'remaining'">
                {{
                    t('auth.register.pwdStrengthRemaining', {
                        count: remainingPasswordCharacters,
                    })
                }}
            </span>
            <span v-else>
                {{ t('auth.register.pwdStrengthContinue') }}
            </span>
        </p>

        <div
            v-if="hasStartedTyping"
            class="mt-2 space-y-2"
        >
            <Progress
                :model-value="progressPercent"
                :indicator-class="allRulesMet ? 'bg-success' : undefined"
            />
            <p class="text-sm font-semibold text-text-muted sm:text-base">
                {{
                    t('auth.register.pwdProgress', {
                        done: fulfilledRulesCount,
                        total: totalRulesCount,
                    })
                }}
            </p>
        </div>

        <ul
            v-if="hasStartedTyping"
            class="mt-2 space-y-2"
            :aria-label="t('auth.register.pwdRulesListAria')"
        >
            <li
                v-for="(rule, index) in rules"
                :key="index"
                :class="
                    cn(
                        'flex items-center gap-3 rounded-xl border-2 px-3 py-2.5 text-base leading-snug sm:py-3 sm:text-lg',
                        rule.met
                            ? 'border-success/50 bg-success/10 font-semibold text-success'
                            : 'border-border bg-surface text-text',
                    )
                "
            >
                <component
                    :is="rule.met ? CircleCheck : Circle"
                    :size="20"
                    class="shrink-0 sm:size-[22px]"
                    :class="rule.met ? 'text-success' : 'text-text-muted'"
                    aria-hidden="true"
                />
                <span>{{ rule.label }}</span>
            </li>
        </ul>
    </section>
</template>
