<?php

declare(strict_types=1);

namespace App\Services\PushReminders;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\User;
use App\Support\PushReminders\PushReminderAudience;
use App\Support\PushReminders\PushReminderRecipient;
use Illuminate\Support\Collection;

final class RecipientsResolver
{
    /**
     * @return list<PushReminderRecipient>
     */
    public function forMedication(Patient $patient, Medication $medication): array
    {
        return $this->forPatient(
            $patient,
            route('patient.inventory', absolute: false),
            (int) $medication->id,
        );
    }

    /**
     * @return list<PushReminderRecipient>
     */
    public function forPrescription(Patient $patient, Medication $medication): array
    {
        return $this->forPatient(
            $patient,
            route('patient.prescriptions', absolute: false),
            (int) $medication->id,
        );
    }

    /**
     * @return list<PushReminderRecipient>
     */
    public function forAppointment(Patient $patient, int $appointmentId): array
    {
        return $this->forPatient(
            $patient,
            route('patient.appointments', ['appointment' => $appointmentId], absolute: false),
            familyOpenUrl: route('family.appointments', [
                'view' => 'planned',
                'appointment' => $appointmentId,
            ], absolute: false),
        );
    }

    /**
     * @return list<PushReminderRecipient>
     */
    public function forPatient(
        Patient $patient,
        string $patientOpenUrl,
        ?int $familyMedicationId = null,
        ?string $familyOpenUrl = null,
    ): array {
        $patient->loadMissing(['user', 'families.user']);

        $recipients = [];
        $patientUser = $patient->user;

        if ($patientUser !== null && $this->hasPushSubscription($patientUser)) {
            $recipients[] = new PushReminderRecipient(
                user: $patientUser,
                audience: PushReminderAudience::Patient,
                openUrl: $patientOpenUrl,
            );
        }

        $patientName = $patientUser?->name;

        foreach ($patient->families as $family) {
            $familyUser = $family->user;

            if ($familyUser === null || ! $this->hasPushSubscription($familyUser)) {
                continue;
            }

            $resolvedFamilyOpenUrl = $familyOpenUrl ?? ($familyMedicationId !== null
                ? route('family.medications', ['medication' => $familyMedicationId], absolute: false)
                : route('family.overview', absolute: false));

            $recipients[] = new PushReminderRecipient(
                user: $familyUser,
                audience: PushReminderAudience::Family,
                openUrl: $resolvedFamilyOpenUrl,
                patientName: $patientName,
            );
        }

        return $recipients;
    }

    /**
     * @return Collection<int, User>
     */
    public function linkedUsersFor(Patient $patient): Collection
    {
        $patient->loadMissing(['user', 'families.user']);

        $users = collect();

        if ($patient->user !== null) {
            $users->push($patient->user);
        }

        foreach ($patient->families as $family) {
            if ($family->user !== null) {
                $users->push($family->user);
            }
        }

        return $users->unique('id')->values();
    }

    private function hasPushSubscription(User $user): bool
    {
        return $user->pushSubscriptions()
            ->where('endpoint', 'not like', '%push.example.test%')
            ->exists();
    }
}
