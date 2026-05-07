<?php

namespace App\Services;

use Carbon\CarbonInterface;
use App\Models\Appointment;
use App\Models\AppointmentTransportInvitation;
use App\Models\Family;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AppointmentTransportInvitationService
{

    public function syncForAppointment(Appointment $appointment, ?array $requestedFamilyIds = null): void
    {
        if (! $appointment->needs_transport) {
            $now = now();

            $this->cancelPendingInvitations($appointment, $now);

            if ($appointment->family_id !== null) {
                $appointment->forceFill([
                    'family_id' => null,
                ])->save();
            }

            return;
        }

        if ($appointment->family_id !== null) {
            return;
        }

        $patient = $appointment->patient()->with('families')->first();

        if ($patient === null) {
            throw new NotFoundHttpException;
        }

        $familyIds = $patient?->families
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all() ?? [];

        $requestedFamilyIds = $requestedFamilyIds === null
            ? $familyIds
            : array_values(array_unique(array_map(fn ($id) => (int) $id, $requestedFamilyIds)));

        $familyIdsToInvite = array_values(array_intersect($familyIds, $requestedFamilyIds));

        if ($familyIdsToInvite === []) {
            return;
        }

        $now = now();

        DB::transaction(function () use ($appointment, $familyIdsToInvite, $now): void {
            Appointment::query()->whereKey($appointment->id)->lockForUpdate()->firstOrFail();

            foreach ($familyIdsToInvite as $familyId) {
                AppointmentTransportInvitation::query()->firstOrCreate(
                    [
                        'appointment_id' => (int) $appointment->id,
                        'family_id' => $familyId,
                    ],
                    [
                        'invited_at' => $now,
                    ],
                );
            }
        });
    }

    public function accept(AppointmentTransportInvitation $invitation, Family $family): void
    {
        $this->assertInvitationOwnedByFamily($invitation, $family);

        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'invitation' => [trans('transport_invitation.not_pending')],
            ]);
        }

        $appointment = Appointment::query()
            ->whereKey($invitation->appointment_id)
            ->with('patient')
            ->firstOrFail();

        $patient = $appointment->patient;

        if ($patient === null || ! $family->patients()->whereKey($patient->id)->exists()) {
            throw new AccessDeniedHttpException;
        }

        $now = now();

        DB::transaction(function () use ($invitation, $family, $now): void {
            $lockedInvitation = AppointmentTransportInvitation::query()
                ->whereKey($invitation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedInvitation->isPending()) {
                throw ValidationException::withMessages([
                    'invitation' => [trans('transport_invitation.not_pending')],
                ]);
            }

            $appointment = Appointment::query()
                ->whereKey($lockedInvitation->appointment_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($appointment->family_id !== null) {
                throw ValidationException::withMessages([
                    'invitation' => [trans('transport_invitation.accept.already_accepted')],
                ]);
            }

            $appointment->forceFill([
                'family_id' => (int) $family->id,
            ])->save();

            $this->cancelPendingInvitations($appointment, $now);

            $lockedInvitation->forceFill([
                'accepted_at' => $now,
                'cancelled_at' => null,
            ])->save();
        });
    }

    public function decline(AppointmentTransportInvitation $invitation, Family $family): void
    {
        $this->assertInvitationOwnedByFamily($invitation, $family);

        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'invitation' => [trans('transport_invitation.not_pending')],
            ]);
        }

        $now = now();

        DB::transaction(function () use ($invitation, $now): void {
            $lockedInvitation = AppointmentTransportInvitation::query()
                ->whereKey($invitation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedInvitation->isPending()) {
                throw ValidationException::withMessages([
                    'invitation' => [trans('transport_invitation.not_pending')],
                ]);
            }

            $lockedInvitation->forceFill([
                'declined_at' => $now,
            ])->save();
        });
    }

    private function assertInvitationOwnedByFamily(AppointmentTransportInvitation $invitation, Family $family): void
    {
        if ((int) $invitation->family_id !== (int) $family->id) {
            throw new NotFoundHttpException;
        }
    }

    private function cancelPendingInvitations(Appointment $appointment, CarbonInterface $now): void
    {
        AppointmentTransportInvitation::query()
            ->where('appointment_id', $appointment->id)
            ->pending()
            ->update(['cancelled_at' => $now]);
    }
}
