import { useForm } from '@inertiajs/vue3';

import { validatePatientEmailField } from '@/lib/validation/validatePatientEmailField';

type CareTeamEmailInviteValidationMessages = {
    required: string;
    invalid: string;
};

export function usePatientFamilyCareTeamEmailInvite(
    storeUrl: () => string,
    validationMessages: () => CareTeamEmailInviteValidationMessages,
) {
    const form = useForm({
        email: '',
    });

    function submitInvite(): void {
        const url = storeUrl();

        if (url === '') {
            return;
        }

        const emailError = validatePatientEmailField(
            form.email,
            validationMessages(),
        );

        if (emailError !== null) {
            form.setError('email', emailError);

            return;
        }

        form.post(url, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                form.clearErrors();
            },
        });
    }

    return {
        form,
        submitInvite,
    };
}
