<script setup lang="ts">
import { computed } from 'vue';
import {
    mobileShellFormFieldInvalidClass,
    mobileShellFormNativeDateTimeInputClass,
    mobileShellFormNativeDateTimePlaceholderClass,
} from '@/lib/shell/mobileShellFormFieldClasses';
import { cn } from '@/lib/utils';

const model = defineModel<string>({ required: true });

const props = withDefaults(
    defineProps<{
        id: string;
        type: 'date' | 'time';
        placeholder: string;
        invalid?: boolean;
        min?: string;
        step?: string | number;
        lang?: string;
        autocomplete?: string;
        ariaDescribedby?: string;
    }>(),
    {
        invalid: false,
        autocomplete: 'off',
    },
);

const isEmpty = computed(() => model.value.trim() === '');
</script>

<template>
    <div class="relative">
        <input
            :id="id"
            v-model="model"
            :type="type"
            :min="min"
            :step="step"
            :lang="lang"
            aria-required="true"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            :class="
                cn(
                    mobileShellFormNativeDateTimeInputClass,
                    isEmpty ? 'text-transparent' : null,
                    props.invalid ? mobileShellFormFieldInvalidClass : null,
                )
            "
            :aria-invalid="props.invalid"
            :aria-describedby="ariaDescribedby"
        />
        <span
            v-if="isEmpty"
            :class="mobileShellFormNativeDateTimePlaceholderClass"
            aria-hidden="true"
        >
            {{ placeholder }}
        </span>
    </div>
</template>
