<?php

use App\Models\Medication;
use App\Models\MedicationStock;
use App\Models\User;
use App\Services\Audit\ActivityLogName;
use Spatie\Activitylog\Models\Activity;

test('updating medication stock records a data activity log', function () {
    $user = User::factory()->patient()->create();
    $medication = Medication::factory()->for($user->patient)->create();
    $stock = MedicationStock::factory()->for($medication)->create([
        'current_stock' => '10',
    ]);

    $this->actingAs($user);

    $stock->update([
        'current_stock' => '5',
    ]);

    $activity = Activity::query()
        ->where('log_name', ActivityLogName::DATA)
        ->where('subject_id', $stock->id)
        ->where('description', 'medication_stock_updated')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($user->id)
        ->and($activity->properties->get('patient_id'))->toBe($user->patient->id)
        ->and($activity->properties->get('stock_changed'))->toBeTrue();
});
