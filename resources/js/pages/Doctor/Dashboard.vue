<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';

const { t } = useI18n();
</script>

<template>
    <Head>
        <title>{{ t('doctor.dashboard.title') }}</title>
    </Head>

    <DoctorLayout>
        <h1 class="text-2xl font-semibold text-text-heading">
            {{ t('doctor.dashboard.heading') }}
        </h1>
    </DoctorLayout>
</template>
