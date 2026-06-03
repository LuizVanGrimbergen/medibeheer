<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Medications\MedicationUrgencyTone;
use App\Models\User;
use App\Support\Medications\MedicationUrgencyToneResolver;
use Illuminate\Http\Request;

final class PatientNavigationState
{
    public static function inertiaPayload(Request $request): array
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isPatient()) {
            return self::emptyPayload();
        }

        $patient = $user->patient;

        if ($patient === null) {
            return self::emptyPayload();
        }

        $resolver = app(MedicationUrgencyToneResolver::class);

        return [
            'inventory' => self::toneValue($resolver->inventoryNavAlertFor($patient)),
            'prescriptions' => self::toneValue($resolver->prescriptionsNavAlertFor($patient)),
        ];
    }

    private static function toneValue(?MedicationUrgencyTone $tone): ?string
    {
        return $tone?->value;
    }

    private static function emptyPayload(): array
    {
        return [
            'inventory' => null,
            'prescriptions' => null,
        ];
    }
}
