<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyDashboardProps } from '@/lib/types';

const props = defineProps<{
    family: FamilyDashboardProps;
}>();

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ t('family.updates.title') }}</title>
    </Head>

    <FamilyLayout>
        <div class="flex flex-col gap-6">
            <h1 class="text-2xl font-semibold text-text-heading">
                {{ t('family.updates.heading') }}
            </h1>
            <p
                v-if="props.family.patients.length > 0 && props.family.active_patient_id !== null"
                class="text-sm text-text-muted"
            >
                {{ t('family.overview.activePatientLabel') }}:
                <span class="font-medium text-text">
                    {{ props.family.patients.find((p) => p.is_active)?.name }}
                </span>
            </p>
            <p
                v-if="!props.family.has_linked_patient"
                class="max-w-prose text-sm leading-relaxed text-text-muted"
            >
                {{ t('family.updates.notLinked') }}
            </p>
            <p
                v-else
                class="text-sm text-text-muted"
            >
                {{ t('family.updates.empty') }}
            </p>
        </div>
    </FamilyLayout>
</template>
