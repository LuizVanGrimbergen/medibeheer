<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ActivePatientBadge from '@/Components/Family/ActivePatientBadge.vue';
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
            <ActivePatientBadge :family="props.family" />
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
