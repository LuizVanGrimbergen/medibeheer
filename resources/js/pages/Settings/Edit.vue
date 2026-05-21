<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { SettingsWidgetLink } from '@/Components/ui/settings-widget-link';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { PageProps, SecurityActivityPaginator } from '@/lib/types';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import PrivacyDataForm from './Partials/PrivacyDataForm.vue';
import SecurityActivityLog from './Partials/SecurityActivityLog.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import PatientMedicationRemindersForm from './Partials/PatientMedicationRemindersForm.vue';
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
const userEmail = computed(() => page.props.auth.user?.email ?? '');
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
        <div class="py-12">
            <div class="mx-auto flex min-h-[calc(100vh-10rem)] max-w-7xl flex-col px-4 sm:px-6 lg:px-8">
                <div
                    v-if="selectedSection === null"
                    class="space-y-4"
                >
                    <SettingsWidgetLink
                        :href="route('settings.edit', { section: 'information' })"
                        rounded-class="rounded-3xl"
                    >
                        <template #leading>
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-full bg-primary text-xl font-bold text-white sm:h-16 sm:w-16"
                            >
                                {{ userInitial }}
                            </div>
                        </template>
                        <p class="text-xl font-bold text-primary">
                            {{ userName }}
                        </p>
                        <p class="text-base text-text-muted">
                            {{ userEmail }}
                        </p>
                    </SettingsWidgetLink>

                    <SettingsWidgetLink
                        :href="route('settings.edit', { section: 'password' })"
                    >
                        <p class="text-lg font-semibold text-primary">
                            {{ t('profile.password.title') }}
                        </p>
                        <p class="mt-1 text-sm text-text-muted">
                            {{ t('profile.password.description') }}
                        </p>
                    </SettingsWidgetLink>

                    <SettingsWidgetLink
                        :href="route('settings.edit', { section: 'privacy-data' })"
                    >
                        <p class="text-lg font-semibold text-primary">
                            {{ t('privacy.settings.title') }}
                        </p>
                        <p class="mt-1 text-sm text-text-muted">
                            {{ t('privacy.settings.description') }}
                        </p>
                    </SettingsWidgetLink>

                    <SettingsWidgetLink
                        v-if="showMedicationRemindersSettings"
                        :href="route('settings.edit', { section: 'medication-reminders' })"
                    >
                        <p class="text-lg font-semibold text-primary">
                            {{ t('patient.medicationReminders.settingsTitle') }}
                        </p>
                        <p class="mt-1 text-sm text-text-muted">
                            {{ t('patient.medicationReminders.settingsLinkDescription') }}
                        </p>
                    </SettingsWidgetLink>

                    <SettingsWidgetLink
                        :href="route('settings.edit', { section: 'security-activity' })"
                    >
                        <p class="text-lg font-semibold text-primary">
                            {{ t('profile.securityActivity.title') }}
                        </p>
                        <p class="mt-1 text-sm text-text-muted">
                            {{ t('profile.securityActivity.description') }}
                        </p>
                    </SettingsWidgetLink>

                    <SettingsWidgetLink
                        :href="route('settings.edit', { section: 'delete' })"
                    >
                        <p class="text-lg font-semibold text-primary">
                            {{ t('profile.delete.title') }}
                        </p>
                        <p class="mt-1 text-sm text-text-muted">
                            {{ t('profile.delete.description') }}
                        </p>
                    </SettingsWidgetLink>
                </div>

                <div
                    v-if="selectedSection !== null"
                    class="space-y-4"
                >
                    <Link
                        :href="route('settings.edit')"
                        class="inline-flex items-center gap-2 rounded-md px-2 py-1 text-sm font-semibold text-primary transition hover:opacity-80"
                    >
                        <ArrowLeft :size="18" />
                        <span>{{ t('profile.backToSettings') }}</span>
                    </Link>

                    <div class="rounded-lg border border-border bg-surface p-4 shadow-sm sm:p-8">
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
                            v-if="selectedSection === 'medication-reminders' && showMedicationRemindersSettings"
                        />

                        <SecurityActivityLog
                            v-if="selectedSection === 'security-activity' && props.securityActivities !== null"
                            :security-activities="props.securityActivities"
                        />

                        <DeleteUserForm
                            v-if="selectedSection === 'delete'"
                        />
                    </div>
                </div>

                <div class="mt-auto flex justify-center pt-8">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="inline-flex items-center rounded-md border border-border bg-surface px-5 py-2.5 text-sm font-semibold text-danger transition duration-150 ease-in-out hover:bg-surface-hover focus:outline-none"
                    >
                        {{ t('app.navigation.logout') }}
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
