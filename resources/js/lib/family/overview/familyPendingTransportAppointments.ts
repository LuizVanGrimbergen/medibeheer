import type { FamilyAcceptedTransportAppointment } from '@/lib/family/overview/familyAcceptedTransportAppointments';

export type FamilyPendingTransportAppointment =
    FamilyAcceptedTransportAppointment & {
        invitation_id: number;
        accept_url: string;
        decline_url: string;
    };
