<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';
import type { PageProps } from '@/lib/types';

const page = usePage<PageProps>();
const { t } = useI18n();
const dismissed = ref(false);

const flashMessage = computed((): string | null => {
    const raw = page.props.flash?.success;

    if (typeof raw !== 'string') {
        return null;
    }

    const trimmed = raw.trim();

    return trimmed === '' ? null : trimmed;
});

watch(flashMessage, () => {
    dismissed.value = false;
});

const open = computed({
    get: () => flashMessage.value !== null && !dismissed.value,
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
    <PatientActionSuccessScreen
        v-model:open="open"
        :title="t('patient.actionSuccess.genericTitle')"
        :message="flashMessage"
        :done-label="t('patient.actionSuccess.done')"
        @done="onDone"
    />
</template>
