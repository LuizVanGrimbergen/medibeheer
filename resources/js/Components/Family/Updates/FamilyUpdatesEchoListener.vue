<script setup lang="ts">
import { useEcho } from '@laravel/echo-vue';
import { reloadFamilyUpdatesPage } from '@/composables/useFamilyUpdatesActions';

const props = defineProps<{
    patientId: number;
}>();

const channelName = `patient.${props.patientId}.family-updates`;

const familyUpdateEvents = ['.daily-checkin.created', '.medication-intake.recorded'] as const;

useEcho(channelName, [...familyUpdateEvents], () => {
    reloadFamilyUpdatesPage();
});
</script>

<template />
