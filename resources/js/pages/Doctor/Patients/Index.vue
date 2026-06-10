<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import DoctorIncomingPatientInvitationsSection from '@/Components/Doctor/Patients/DoctorIncomingPatientInvitationsSection.vue';
import DoctorLinkedPatientsSection from '@/Components/Doctor/Patients/DoctorLinkedPatientsSection.vue';
import ListCardSkeleton from '@/Components/ui/skeleton/ListCardSkeleton.vue';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';
import { areAnyDeferredInertiaPropsLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import type { IncomingDoctorInvitation, LinkedPatient } from '@/lib/types';

const props = defineProps<{
    patients?: LinkedPatient[];
    incoming_invitations?: IncomingDoctorInvitation[];
}>();

const { t } = useI18n();

const isPatientsLoading = computed(() =>
    areAnyDeferredInertiaPropsLoading(
        props.patients,
        props.incoming_invitations,
    ),
);
</script>

<template>
    <Head>
        <title>{{ t('doctor.patients.title') }}</title>
    </Head>

    <DoctorLayout>
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 md:gap-5">
            <h1 class="text-text-heading text-2xl font-semibold">
                {{ t('doctor.patients.heading') }}
            </h1>

            <ListCardSkeleton v-if="isPatientsLoading" />

            <template v-else>
                <DoctorLinkedPatientsSection
                    :patients="props.patients ?? []"
                />

                <DoctorIncomingPatientInvitationsSection
                    :invitations="props.incoming_invitations ?? []"
                />
            </template>
        </div>
    </DoctorLayout>
</template>
