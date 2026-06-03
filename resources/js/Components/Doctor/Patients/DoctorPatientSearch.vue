<script setup lang="ts">
import { Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import DoctorCollapsibleSection from '@/Components/Doctor/Patients/DoctorCollapsibleSection.vue';
import { SearchField } from '@/Components/ui/search-field';
import { SelectableNavLink } from '@/Components/ui/selectable-nav-link';
import {
    doctorPatientMatchesSearchQuery,
    normalizeDoctorPatientSearchQuery,
} from '@/lib/doctor/patients/normalizeDoctorPatientSearchQuery';

const props = withDefaults(
    defineProps<{
        patients: { public_id: string; name: string }[];
        selectedPatientPublicId?: string | null;
        autofocus?: boolean;
    }>(),
    {
        selectedPatientPublicId: null,
        autofocus: false,
    },
);

const { t } = useI18n();
const searchQuery = ref('');
const isOpen = ref(props.selectedPatientPublicId === null);

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
    () =>
        hasPatients.value &&
        hasSearchQuery.value &&
        filteredPatients.value.length === 0,
);

const selectedPatient = computed(() => {
    if (props.selectedPatientPublicId === null) {
        return null;
    }

    return (
        props.patients.find(
            (patient) => patient.public_id === props.selectedPatientPublicId,
        ) ?? null
    );
});

const collapsedSummary = computed((): string => {
    if (!hasPatients.value) {
        return t('doctor.patients.empty');
    }

    if (selectedPatient.value !== null) {
        return selectedPatient.value.name;
    }

    if (props.patients.length === 1) {
        return props.patients[0]?.name ?? '';
    }

    return t('doctor.patients.collapsedCount', {
        count: String(props.patients.length),
    });
});
</script>

<template>
    <DoctorCollapsibleSection
        v-model:open="isOpen"
        :heading="t('doctor.patients.searchHeading')"
        :toggle-label="t('doctor.patients.searchToggle')"
        :collapsed-summary="collapsedSummary"
    >
        <template #icon>
            <Users class="size-5" />
        </template>

        <SearchField
            id="doctor-patient-search"
            v-model="searchQuery"
            name="doctor-patient-search"
            :autofocus="props.autofocus && isOpen"
            :placeholder="t('doctor.patients.searchPlaceholder')"
            :clear-label="t('doctor.patients.searchClear')"
        />

        <p v-if="!hasPatients" class="text-text-muted text-sm leading-relaxed">
            {{ t('doctor.patients.empty') }}
        </p>

        <p
            v-else-if="showNoResults"
            class="text-text-muted text-sm leading-relaxed"
        >
            {{
                t('doctor.patients.searchNoResults', {
                    query: searchQuery.trim(),
                })
            }}
        </p>

        <ul v-else class="flex flex-col gap-3 md:gap-2.5" role="list">
            <li v-for="patient in filteredPatients" :key="patient.public_id">
                <SelectableNavLink
                    :href="
                        route('doctor.dashboard', {
                            patient: patient.public_id,
                        })
                    "
                    :selected="
                        props.selectedPatientPublicId === patient.public_id
                    "
                >
                    {{ patient.name }}
                </SelectableNavLink>
            </li>
        </ul>
    </DoctorCollapsibleSection>
</template>
