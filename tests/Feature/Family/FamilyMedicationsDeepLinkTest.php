<?php

use App\Models\Medication;
use App\Models\User;

test('family medications opens the paginated page for a deep linked medication', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $target = Medication::factory()->for($patient)->create([
        'name' => 'Magnesiumcitraat',
    ]);

    foreach (range(1, 11) as $index) {
        Medication::factory()->for($patient)->create([
            'name' => "Medicatie {$index}",
        ]);
    }

    $this->actingAs($familyUser)
        ->get(route('family.medications', ['medication' => $target->id]))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->component('Family/Medications/Index')
            ->where('medications.meta.current_page', 2)
            ->where('medications.data.1.id', $target->id)
            ->where('medications.data.1.name', 'Magnesiumcitraat')));
});

test('family medications ignores invalid deep link medication ids', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    Medication::factory()->for($patient)->create();

    $this->actingAs($familyUser)
        ->get(route('family.medications', ['medication' => 999_999]))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->component('Family/Medications/Index')
            ->where('medications.meta.current_page', 1)));
});
