<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import { SettingsWidgetLink } from '@/Components/ui/settings-widget-link';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { PageProps, SecurityActivityPaginator } from '@/lib/types';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import PatientMedicationRemindersForm from './Partials/PatientMedicationRemindersForm.vue';
import PrivacyDataForm from './Partials/PrivacyDataForm.vue';
import SecurityActivityLog from './Partials/SecurityActivityLog.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

type SettingsSection =
    | 'information'
    | 'password'
    | 'delete'
    | 'security-activity'
    | 'privacy-data'
    | 'medication-reminders';

const { t } = useI18n();
const page = usePage<PageProps>();

const sectionKeys = new Set<SettingsSection>([
    'information',
    'password',
    'privacy-data',
    'medication-reminders',
    'delete',
    'security-activity',
]);

const isPatient = computed(() => page.props.auth.user?.role === 'patient');
const isFamilyMember = computed(
    () => page.props.auth.user?.role === 'family_member',
);

const settingsBackHref = computed(() => {
    if (isPatient.value) {
        return route('patient.dashboard');
    }

    if (isFamilyMember.value) {
        return route('family.overview');
    }

    if (page.props.auth.user?.role === 'doctor') {
        return route('doctor.dashboard');
    }

    return route('home');
});

const settingsBackLabelKey = computed(() => {
    if (isPatient.value) {
        return 'patient.navigation.home';
    }

    if (isFamilyMember.value) {
        return 'family.navigation.overview';
    }

    return 'doctor.navigation.home';
});
const showMedicationRemindersSettings = computed(
    () => isPatient.value && page.props.webpush !== undefined,
);
const selectedSection = computed<SettingsSection | null>(() => {
    const [, search = ''] = page.url.split('?');
    const section = new URLSearchParams(search).get('section');

    if (section === null) {
        return null;
    }

    if (!sectionKeys.has(section as SettingsSection)) {
        return null;
    }

    return section as SettingsSection;
});

const userName = computed(() => page.props.auth.user?.name ?? '');
const userInitial = computed(() => userName.value.charAt(0).toUpperCase());

const props = defineProps<{
    mustVerifyEmail: boolean;
    status?: string;
    securityActivities: SecurityActivityPaginator | null;
}>();
</script>

<template>
    <Head>
        <title>{{ t('app.navigation.settings') }}</title>
    </Head>

    <AuthenticatedLayout>
        <div
            class="min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain"
        >
            <div class="py-12">
                <div
                    class="mx-auto flex min-h-[calc(100vh-10rem)] max-w-7xl flex-col px-4 sm:px-6 lg:px-8"
                >
                    <Link
                        v-if="isPatient || isFamilyMember"
                        :href="settingsBackHref"
                        class="text-primary mb-4 inline-flex items-center gap-2 rounded-md px-2 py-1 text-sm font-semibold transition hover:opacity-80 md:hidden"
                    >
                        <ArrowLeft :size="18" />
                        <span>{{ t(settingsBackLabelKey) }}</span>
                    </Link>

                    <div v-if="selectedSection === null" class="space-y-4">
                        <SettingsWidgetLink
                            :href="
                                route('settings.edit', {
                                    section: 'information',
                                })
                            "
                            rounded-class="rounded-3xl"
                        >
                            <template #leading>
                                <div
                                    class="bg-primary flex h-14 w-14 items-center justify-center rounded-full text-xl font-bold text-white sm:h-16 sm:w-16"
                                >
                                    {{ userInitial }}
                                </div>
                            </template>
                            <p class="text-primary text-xl font-bold">
                                {{ userName }}
                            </p>
                        </SettingsWidgetLink>

                        <SettingsWidgetLink
                            :href="
                                route('settings.edit', { section: 'password' })
                            "
                        >
                            <p class="text-primary text-lg font-semibold">
                                {{ t('profile.password.title') }}
                            </p>
                            <p class="text-text-muted mt-1 text-sm">
                                {{ t('profile.password.description') }}
                            </p>
                        </SettingsWidgetLink>

                        <SettingsWidgetLink
                            :href="
                                route('settings.edit', {
                                    section: 'privacy-data',
                                })
                            "
                        >
                            <p class="text-primary text-lg font-semibold">
                                {{ t('privacy.settings.title') }}
                            </p>
                            <p class="text-text-muted mt-1 text-sm">
                                {{ t('privacy.settings.description') }}
                            </p>
                        </SettingsWidgetLink>

                        <SettingsWidgetLink
                            v-if="showMedicationRemindersSettings"
                            :href="
                                route('settings.edit', {
                                    section: 'medication-reminders',
                                })
                            "
                        >
                            <p class="text-primary text-lg font-semibold">
                                {{
                                    t(
                                        'patient.medicationReminders.settingsTitle',
                                    )
                                }}
                            </p>
                            <p class="text-text-muted mt-1 text-sm">
                                {{
                                    t(
                                        'patient.medicationReminders.settingsLinkDescription',
                                    )
                                }}
                            </p>
                        </SettingsWidgetLink>

                        <SettingsWidgetLink
                            :href="
                                route('settings.edit', {
                                    section: 'security-activity',
                                })
                            "
                        >
                            <p class="text-primary text-lg font-semibold">
                                {{ t('profile.securityActivity.title') }}
                            </p>
                            <p class="text-text-muted mt-1 text-sm">
                                {{ t('profile.securityActivity.description') }}
                            </p>
                        </SettingsWidgetLink>

                        <SettingsWidgetLink
                            :href="
                                route('settings.edit', { section: 'delete' })
                            "
                        >
                            <p class="text-primary text-lg font-semibold">
                                {{ t('profile.delete.title') }}
                            </p>
                            <p class="text-text-muted mt-1 text-sm">
                                {{ t('profile.delete.description') }}
                            </p>
                        </SettingsWidgetLink>
                    </div>

                    <div v-if="selectedSection !== null" class="space-y-4">
                        <Link
                            :href="route('settings.edit')"
                            class="text-primary inline-flex items-center gap-2 rounded-md px-2 py-1 text-sm font-semibold transition hover:opacity-80"
                        >
                            <ArrowLeft :size="18" />
                            <span>{{ t('profile.backToSettings') }}</span>
                        </Link>

                        <div
                            class="border-border bg-surface rounded-2xl border p-4 shadow-sm sm:p-6"
                        >
                            <UpdateProfileInformationForm
                                v-if="selectedSection === 'information'"
                                :must-verify-email="props.mustVerifyEmail"
                                :status="props.status"
                            />

                            <UpdatePasswordForm
                                v-if="selectedSection === 'password'"
                            />

                            <PrivacyDataForm
                                v-if="selectedSection === 'privacy-data'"
                            />

                            <PatientMedicationRemindersForm
                                v-if="
                                    selectedSection ===
                                        'medication-reminders' &&
                                    showMedicationRemindersSettings
                                "
                            />

                            <SecurityActivityLog
                                v-if="
                                    selectedSection === 'security-activity' &&
                                    props.securityActivities !== null
                                "
                                :security-activities="props.securityActivities"
                            />

                            <DeleteUserForm
                                v-if="selectedSection === 'delete'"
                            />
                        </div>
                    </div>

                    <div class="mt-auto pt-8">
                        <Button
                            as-child
                            variant="outline"
                            class="h-auto min-h-12 w-full touch-manipulation text-danger hover:bg-surface-hover hover:text-danger sm:min-h-14"
                        >
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                {{ t('app.navigation.logout') }}
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
