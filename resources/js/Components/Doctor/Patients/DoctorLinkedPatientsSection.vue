<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import DoctorCollapsibleSection from '@/Components/Doctor/Patients/DoctorCollapsibleSection.vue';
import PatientFamilyCareTeamRowItem from '@/Components/Patient/Family/PatientFamilyCareTeamRowItem.vue';
import { SearchField } from '@/Components/ui/search-field';
import {
    doctorPatientMatchesSearchQuery,
    normalizeDoctorPatientSearchQuery,
} from '@/lib/doctor/patients/normalizeDoctorPatientSearchQuery';
import type { LinkedPatient } from '@/lib/types';

const props = defineProps<{
    patients: LinkedPatient[];
}>();

const { t } = useI18n();
const isOpen = ref(false);
const searchQuery = ref('');

const normalizedSearchQuery = computed(() =>
    normalizeDoctorPatientSearchQuery(searchQuery.value),
);

const filteredPatients = computed(() => {
    const query = normalizedSearchQuery.value;

    return props.patients.filter((patient) =>
        doctorPatientMatchesSearchQuery(patient.name, query),
    );
});

const hasPatients = computed(() => props.patients.length > 0);

const hasSearchQuery = computed(() => normalizedSearchQuery.value !== '');

const showNoResults = computed(
    () => hasPatients.value && hasSearchQuery.value && filteredPatients.value.length === 0,
);

const collapsedSummary = computed((): string => {
    if (!hasPatients.value) {
        return t('doctor.patients.empty');
    }

    if (props.patients.length === 1) {
        return t('doctor.patients.linkedCollapsedOne');
    }

    return t('doctor.patients.linkedCollapsedMany', {
        count: String(props.patients.length),
    });
});

function unlinkPatient(patient: LinkedPatient): void {
    router.delete(patient.unlink_url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <DoctorCollapsibleSection
        v-model:open="isOpen"
        :heading="t('doctor.patients.linkedHeading')"
        :toggle-label="t('doctor.patients.linkedToggle')"
        :collapsed-summary="collapsedSummary"
    >
        <template #icon>
            <Users class="size-5" />
        </template>

        <SearchField
            v-if="hasPatients"
            id="doctor-linked-patient-search"
            v-model="searchQuery"
            name="doctor-linked-patient-search"
            :autofocus="isOpen"
            :placeholder="t('doctor.patients.searchPlaceholder')"
            :clear-label="t('doctor.patients.searchClear')"
        />

        <p
            v-if="!hasPatients"
            class="text-sm leading-relaxed text-text-muted"
        >
            {{ t('doctor.patients.empty') }}
        </p>

        <p
            v-else-if="showNoResults"
            class="text-sm leading-relaxed text-text-muted"
        >
            {{ t('doctor.patients.searchNoResults', { query: searchQuery.trim() }) }}
        </p>

        <div
            v-else
            class="flex flex-col gap-3 md:gap-2.5"
        >
            <PatientFamilyCareTeamRowItem
                v-for="patient in filteredPatients"
                :key="patient.public_id"
                density="compact"
                :title="patient.name"
                :action-label="t('doctor.patients.unlink')"
                :confirm-title="t('doctor.patients.unlinkConfirmTitle')"
                :confirm-description="
                    t('doctor.patients.unlinkConfirmMessage', { name: patient.name })
                "
                :confirm-label="t('doctor.patients.unlinkConfirmAction')"
                :cancel-label="t('doctor.patients.cancel')"
                @action="unlinkPatient(patient)"
            />
        </div>
    </DoctorCollapsibleSection>
</template>
