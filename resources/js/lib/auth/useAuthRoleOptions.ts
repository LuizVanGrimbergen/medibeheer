import { Stethoscope, UserRound, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { RoleOption } from '@/lib/types';

export function useAuthRoleOptions() {
    const { t } = useI18n();

    return computed((): readonly RoleOption[] => {
        return [
            {
                key: 'patient',
                label: t('auth.common.roles.patient'),
                icon: UserRound,
                ringClass:
                    'border-role-patient/60 bg-role-patient/10 text-role-patient',
            },
            {
                key: 'doctor',
                label: t('auth.common.roles.doctor'),
                icon: Stethoscope,
                ringClass:
                    'border-role-doctor/50 bg-role-doctor/10 text-role-doctor',
            },
            {
                key: 'family_member',
                label: t('auth.common.roles.family_member'),
                icon: Users,
                ringClass:
                    'border-role-family/60 bg-role-family/10 text-role-family',
            },
        ];
    });
}
