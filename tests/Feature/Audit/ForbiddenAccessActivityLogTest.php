<?php

use App\Enums\SecurityActivityDescription;
use App\Models\Medication;
use App\Models\User;
use App\Services\Audit\ActivityLogName;
use Spatie\Activitylog\Models\Activity;

test('policy denial records authorization_denied security activity', function () {
    $owner = User::factory()->patient()->create();
    $other = User::factory()->patient()->create();
    $medication = Medication::factory()->for($owner->patient)->create();

    $this->actingAs($other)->put(
        route('patient.medications.update', $medication),
        [
            'name' => 'Gestolen',
            'type_medication' => 'pill',
        ],
    );

    $activity = Activity::query()
        ->where('log_name', ActivityLogName::SECURITY)
        ->where('description', SecurityActivityDescription::AUTHORIZATION_DENIED->value)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($other->id)
        ->and($activity->properties->get('ability'))->toBe('update')
        ->and($activity->subject_id)->toBe($medication->id);
});

test('role middleware denial records access_forbidden security activity', function () {
    $user = User::factory()->patient()->create();

    $this->actingAs($user)->get(route('doctor.dashboard'));

    $activity = Activity::query()
        ->where('log_name', ActivityLogName::SECURITY)
        ->where('description', SecurityActivityDescription::ACCESS_FORBIDDEN->value)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($user->id);
});
