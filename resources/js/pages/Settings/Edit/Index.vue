<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientConfirmDialog from '@/Components/Patient/PatientConfirmDialog.vue';
import { Button } from '@/Components/ui/button';
import { SettingsWidgetLink } from '@/Components/ui/settings-widget-link';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    mobileShellFormWizardFooterCancelButtonClass,
    mobileShellFormWizardFooterPrimaryButtonClass,
    mobileShellFormWizardFooterRowClass,
    mobileShellWizardFooterClass,
} from '@/lib/shell/mobileShellDialogLayout';
import type { PageProps, SecurityActivityPaginator } from '@/lib/types';
import DeleteUserForm from '../Partials/DeleteUserForm.vue';
import PatientMedicationRemindersForm from '../Partials/PatientMedicationRemindersForm.vue';
import PrivacyDataForm from '../Partials/PrivacyDataForm.vue';
import SecurityActivityLog from '../Partials/SecurityActivityLog.vue';
import UpdatePasswordForm from '../Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '../Partials/UpdateProfileInformationForm.vue';

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

const settingsOverviewBackLabelKey = computed(() => {
    if (isPatient.value) {
        return 'profile.backToHome';
    }

    if (isFamilyMember.value) {
        return 'profile.backToOverview';
    }

    return 'profile.backToDoctorHome';
});
const showSettingsBackButton = computed(() => true);
const showMedicationRemindersSettings = computed(
    () =>
        (isPatient.value || isFamilyMember.value) &&
        page.props.webpush !== undefined,
);
const medicationRemindersRole = computed((): 'patient' | 'family_member' =>
    isFamilyMember.value ? 'family_member' : 'patient',
);
const medicationRemindersTranslationPrefix = computed(() =>
    isFamilyMember.value
        ? 'family.medicationReminders'
        : 'patient.medicationReminders',
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

const confirmingLogout = ref(false);
const logoutProcessing = ref(false);

function openLogoutConfirm(): void {
    confirmingLogout.value = true;
}

function closeLogoutConfirm(): void {
    confirmingLogout.value = false;
}

function confirmLogout(): void {
    logoutProcessing.value = true;

    router.post(
        route('logout'),
        {},
        {
            onFinish: () => {
                logoutProcessing.value = false;
                closeLogoutConfirm();
            },
        },
    );
}
</script>

<template>
    <Head>
        <title>{{ t('app.navigation.settings') }}</title>
    </Head>

    <AuthenticatedLayout>
        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
            <div
                class="h-0 min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain"
            >
                <div
                    class="mx-auto flex min-h-full w-full max-w-7xl flex-col px-4 py-4 sm:px-6 md:py-6 lg:px-8"
                >
                    <div
                        v-if="selectedSection === null"
                        class="min-h-0 flex-1 space-y-4"
                    >
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
                                        `${medicationRemindersTranslationPrefix}.settingsTitle`,
                                    )
                                }}
                            </p>
                            <p class="text-text-muted mt-1 text-sm">
                                {{
                                    t(
                                        `${medicationRemindersTranslationPrefix}.settingsLinkDescription`,
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

                    <div
                        v-if="selectedSection !== null"
                        class="min-h-0 flex-1 space-y-4"
                    >
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
                                :role="medicationRemindersRole"
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

                    <div :class="mobileShellWizardFooterClass">
                        <div :class="mobileShellFormWizardFooterRowClass">
                            <Button
                                v-if="showSettingsBackButton"
                                as-child
                                variant="default"
                                size="lg"
                                :class="
                                    mobileShellFormWizardFooterPrimaryButtonClass
                                "
                            >
                                <Link :href="settingsBackHref">
                                    {{ t(settingsOverviewBackLabelKey) }}
                                </Link>
                            </Button>
                            <Button
                                type="button"
                                variant="secondary"
                                size="lg"
                                :class="
                                    mobileShellFormWizardFooterCancelButtonClass
                                "
                                @click="openLogoutConfirm"
                            >
                                {{ t('app.navigation.logout') }}
                            </Button>
                        </div>
                    </div>

                    <PatientConfirmDialog
                        :open="confirmingLogout"
                        :title="t('app.navigation.logoutConfirm.title')"
                        :description="t('app.navigation.logoutConfirm.message')"
                        :confirm-label="
                            t('app.navigation.logoutConfirm.confirm')
                        "
                        :cancel-label="t('app.navigation.logoutConfirm.cancel')"
                        :processing="logoutProcessing"
                        :icon="LogOut"
                        icon-tone="danger"
                        cancel-first
                        cancel-tone="primary"
                        @update:open="
                            (open) => {
                                confirmingLogout = open;
                            }
                        "
                        @confirm="confirmLogout"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
