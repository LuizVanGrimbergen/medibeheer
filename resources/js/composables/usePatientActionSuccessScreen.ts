import { ref } from 'vue';

export type PatientActionSuccessDetail = {
    label: string;
    value: string;
};

export type PatientActionSuccessScreenContent = {
    title: string;
    message?: string | null;
    eyebrow?: string | null;
    subtitle?: string | null;
    details?: PatientActionSuccessDetail[];
};

export function usePatientActionSuccessScreen() {
    const open = ref(false);
    const title = ref('');
    const message = ref<string | null>(null);
    const eyebrow = ref<string | null>(null);
    const subtitle = ref<string | null>(null);
    const details = ref<PatientActionSuccessDetail[]>([]);

    function show(content: PatientActionSuccessScreenContent): void {
        title.value = content.title;
        message.value = content.message ?? null;
        eyebrow.value = content.eyebrow ?? null;
        subtitle.value = content.subtitle ?? null;
        details.value = content.details ?? [];
        open.value = true;
    }

    function dismiss(): void {
        open.value = false;
    }

    return {
        open,
        title,
        message,
        eyebrow,
        subtitle,
        details,
        show,
        dismiss,
    };
}
