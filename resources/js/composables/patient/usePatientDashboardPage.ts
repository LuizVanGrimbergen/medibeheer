import { router, usePage } from '@inertiajs/vue3';
import { useDocumentVisibility } from '@vueuse/core';
import { computed, onMounted, onUnmounted, watch, type ComputedRef } from 'vue';
import { isDeferredInertiaPropLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import type { PatientDashboardScreenProps } from '@/lib/patient/dashboard/patientDashboardScreenProps';
import type { DailyMoodScoreValue, PageProps } from '@/lib/types';

const pushMarkSuccessRoute = route('patient.medication-push-mark.success');

function redirectToPushSuccessIfPending(name: string | null | undefined): void {
    if (typeof name !== 'string' || name === '') {
        return;
    }

    router.visit(pushMarkSuccessRoute, { replace: true });
}

function localCalendarDateIso(): string {
    const now = new Date();

    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');

    return `${y}-${m}-${d}`;
}

export function usePatientDashboardPage(props: PatientDashboardScreenProps): {
    isTodayCheckinLoading: ComputedRef<boolean>;
    isTodayMedicationIntakesLoading: ComputedRef<boolean>;
    showMedicationOnboardingShortcuts: ComputedRef<boolean>;
    dailyCheckinMoodFlash: ComputedRef<DailyMoodScoreValue | null>;
    showDailyCheckinCard: ComputedRef<boolean>;
    dailyCheckinEncouragementFlash: ComputedRef<string | null>;
} {
    const page = usePage<PageProps>();

    const isTodayCheckinLoading = computed(() =>
        isDeferredInertiaPropLoading(props.today_checkin),
    );

    const isTodayMedicationIntakesLoading = computed(() =>
        isDeferredInertiaPropLoading(props.today_medication_intakes),
    );

    const showMedicationOnboardingShortcuts = computed(
        () => !props.has_medications,
    );

    const dailyCheckinMoodFlash = computed((): DailyMoodScoreValue | null => {
        const raw = page.props.flash?.daily_checkin_mood;

        if (raw === 'bad' || raw === 'ok' || raw === 'good') {
            return raw;
        }

        return null;
    });

    const showDailyCheckinCard = computed(
        () =>
            !isTodayCheckinLoading.value &&
            props.today_checkin === null &&
            dailyCheckinMoodFlash.value === null,
    );

    const dailyCheckinEncouragementFlash = computed((): string | null => {
        const raw = page.props.flash?.daily_checkin_encouragement;

        if (typeof raw !== 'string') {
            return null;
        }

        const trimmed = raw.trim();

        return trimmed === '' ? null : trimmed;
    });

    function hasCalendarDayAdvanced(): boolean {
        return localCalendarDateIso() !== props.today_date;
    }

    function reloadDashboardState(): void {
        if (hasCalendarDayAdvanced()) {
            router.reload();

            return;
        }

        router.reload({
            only: [
                'today_date',
                'today_checkin',
                'today_medication_intakes',
                'pending_push_medication_mark',
            ],
            onSuccess: (reloadedPage) => {
                redirectToPushSuccessIfPending(
                    reloadedPage.props.pending_push_medication_mark as
                        | string
                        | null
                        | undefined,
                );
            },
        });
    }

    const documentVisibility = useDocumentVisibility();

    watch(documentVisibility, (state) => {
        if (state !== 'visible') {
            return;
        }

        reloadDashboardState();
    });

    function onWindowPageShow(): void {
        if (document.visibilityState !== 'visible') {
            return;
        }

        reloadDashboardState();
    }

    let pushMarkBroadcastChannel: BroadcastChannel | null = null;

    onMounted(() => {
        window.addEventListener('pageshow', onWindowPageShow);

        redirectToPushSuccessIfPending(props.pending_push_medication_mark);

        if ('BroadcastChannel' in globalThis) {
            pushMarkBroadcastChannel = new BroadcastChannel(
                'medibeheer-medication-push-mark',
            );
            pushMarkBroadcastChannel.onmessage = () => {
                router.visit(pushMarkSuccessRoute, { replace: true });
            };
        }
    });

    onUnmounted(() => {
        window.removeEventListener('pageshow', onWindowPageShow);

        pushMarkBroadcastChannel?.close();
    });

    return {
        isTodayCheckinLoading,
        isTodayMedicationIntakesLoading,
        showMedicationOnboardingShortcuts,
        dailyCheckinMoodFlash,
        showDailyCheckinCard,
        dailyCheckinEncouragementFlash,
    };
}
