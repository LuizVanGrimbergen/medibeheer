<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import ActionSuccessScreen from '@/Components/ui/action-success-screen/ActionSuccessScreen.vue';
import type { PageProps } from '@/lib/types';

const props = defineProps<{
    message?: string | null;
}>();

const page = usePage<PageProps>();
const { t } = useI18n();
const dismissed = ref(false);

const sessionMessage = computed((): string | null => {
    if (props.message !== undefined) {
        if (typeof props.message !== 'string') {
            return null;
        }

        const trimmed = props.message.trim();

        return trimmed === '' ? null : trimmed;
    }

    const raw = page.props.flash?.success;

    if (typeof raw !== 'string') {
        return null;
    }

    const trimmed = raw.trim();

    return trimmed === '' ? null : trimmed;
});

watch(sessionMessage, () => {
    dismissed.value = false;
});

const open = computed({
    get: () => sessionMessage.value !== null && !dismissed.value,
    set: (value: boolean) => {
        if (!value) {
            dismissed.value = true;
        }
    },
});

function onDone(): void {
    dismissed.value = true;
}
</script>

<template>
    <ActionSuccessScreen
        v-if="sessionMessage !== null"
        v-model:open="open"
        :title="sessionMessage"
        :done-label="t('app.actionSuccess.done')"
        @done="onDone"
    />
</template>
