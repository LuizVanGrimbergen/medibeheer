<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ t('family.overview.title') }}</title>
    </Head>

    <FamilyLayout>
        <h1 class="text-2xl font-semibold text-text-heading">
            {{ t('family.overview.heading') }}
        </h1>
    </FamilyLayout>
</template>
