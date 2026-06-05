<?php

use App\Enums\MedicationPlanProposalStatus;
use App\Mail\MedicationPlanProposalMail;
use App\Models\Medication;
use App\Models\MedicationPlanProposal;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('family members can create a medication plan draft without a linked patient', function () {
    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->familyOrCreate();

    $response = $this->actingAs($familyUser)->post(route('family.medication-plans.store'), validMedicationPlanProposalPayload());

    $response->assertRedirect();

    $proposal = MedicationPlanProposal::query()
        ->where('family_id', $family->id)
        ->first();

    expect($proposal)->not->toBeNull();
    expect($proposal->patient_id)->toBeNull();
    expect($proposal->status)->toBe(MedicationPlanProposalStatus::DRAFT);
    expect($proposal->items)->toHaveCount(1);
});

test('family members can add another medication to a draft plan', function () {
    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->familyOrCreate();

    $proposal = MedicationPlanProposal::factory()
        ->forFamily($family)
        ->withMedicationItem()
        ->create();

    $this->actingAs($familyUser)
        ->post(route('family.medication-plans.items.store', $proposal), validMedicationPlanProposalPayload([
            'name' => 'Paracetamol',
        ]))
        ->assertRedirect();

    expect($proposal->fresh()->items)->toHaveCount(2);
});

test('family members can publish a draft by sending email to the patient', function () {
    Mail::fake();

    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->familyOrCreate();
    $patientEmail = 'plan-patient-'.uniqid('', true).'@example.com';

    $proposal = MedicationPlanProposal::factory()
        ->forFamily($family)
        ->withMedicationItem()
        ->create();

    $response = $this->actingAs($familyUser)->post(route('family.medication-plans.publish.store', $proposal), [
        'patient_email' => $patientEmail,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertSent(MedicationPlanProposalMail::class, function (MedicationPlanProposalMail $mail) use ($patientEmail): bool {
        return $mail->hasTo($patientEmail);
    });

    expect($proposal->fresh()->status)->toBe(MedicationPlanProposalStatus::PUBLISHED);
    expect($proposal->fresh()->invited_patient_email)->toBe($patientEmail);
});

test('publish rejects an invalid patient email', function () {
    Mail::fake();

    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->familyOrCreate();

    $proposal = MedicationPlanProposal::factory()
        ->forFamily($family)
        ->withMedicationItem()
        ->create();

    $this->actingAs($familyUser)
        ->from(route('family.medication-plans.publish', $proposal))
        ->post(route('family.medication-plans.publish.store', $proposal), [
            'patient_email' => 'geen-geldig-adres',
        ])
        ->assertRedirect(route('family.medication-plans.publish', $proposal))
        ->assertSessionHasErrors([
            'patient_email' => trans('medication_plan_proposal.publish.validation.patient_email_invalid'),
        ]);

    Mail::assertNothingSent();
    expect($proposal->fresh()->status)->toBe(MedicationPlanProposalStatus::DRAFT);
});

test('family link page exposes patient email on published proposals', function () {
    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->familyOrCreate();
    $patientEmail = 'plan-link-'.uniqid('', true).'@example.com';

    MedicationPlanProposal::factory()
        ->forFamily($family)
        ->withMedicationItem()
        ->published()
        ->create([
            'invited_patient_email' => $patientEmail,
            'invited_patient_email_hash' => User::hashEmail($patientEmail),
        ]);

    $this->actingAs($familyUser)
        ->get(route('family.link'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/Link')
            ->has('proposals', 1)
            ->where('proposals.0.patient_email', $patientEmail));
});

test('family members can view the publish page', function () {
    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->familyOrCreate();

    $proposal = MedicationPlanProposal::factory()
        ->forFamily($family)
        ->withMedicationItem()
        ->create();

    $this->actingAs($familyUser)
        ->get(route('family.medication-plans.publish', $proposal))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/MedicationPlans/Publish')
            ->where('proposal_id', $proposal->id));
});

test('patients see pending medication plans on the family page when emailed', function () {
    $patientEmail = 'plan-pending-'.uniqid('', true).'@example.com';
    $patientUser = User::factory()->patient()->create([
        'email' => $patientEmail,
        'email_verified_at' => now(),
    ]);

    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->familyOrCreate();

    MedicationPlanProposal::factory()
        ->forFamily($family)
        ->withMedicationItem()
        ->published()
        ->create([
            'invited_patient_email' => $patientEmail,
            'invited_patient_email_hash' => User::hashEmail($patientEmail),
        ]);

    $this->actingAs($patientUser)
        ->get(route('patient.family'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Family')
            ->has('pending_medication_plans', 1)
            ->where('pending_medication_plans.0.family_member_name', $familyUser->name));
});

test('patients can accept a published plan from the family page list', function () {
    $patientEmail = 'plan-list-accept-'.uniqid('', true).'@example.com';
    $patientUser = User::factory()->patient()->create([
        'email' => $patientEmail,
        'email_verified_at' => now(),
    ]);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->familyOrCreate();

    $proposal = MedicationPlanProposal::factory()
        ->forFamily($family)
        ->withMedicationItem()
        ->published()
        ->create([
            'invited_patient_email' => $patientEmail,
            'invited_patient_email_hash' => User::hashEmail($patientEmail),
        ]);

    $this->actingAs($patientUser)
        ->post(route('patient.medication-plans.accept', $proposal))
        ->assertRedirect(route('patient.medications'));

    expect($proposal->fresh()->status)->toBe(MedicationPlanProposalStatus::ACCEPTED);
});

test('patients can review and accept a published plan from the family page', function () {
    $patientEmail = 'plan-accept-'.uniqid('', true).'@example.com';
    $patientUser = User::factory()->patient()->create([
        'email' => $patientEmail,
        'email_verified_at' => now(),
    ]);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->familyOrCreate();

    $proposal = MedicationPlanProposal::factory()
        ->forFamily($family)
        ->withMedicationItem()
        ->published()
        ->create([
            'invited_patient_email' => $patientEmail,
            'invited_patient_email_hash' => User::hashEmail($patientEmail),
        ]);

    $this->actingAs($patientUser)
        ->get(route('patient.medication-plans.review', $proposal))
        ->assertOk();

    $this->actingAs($patientUser)
        ->post(route('patient.medication-plans.accept', $proposal))
        ->assertRedirect(route('patient.medications'));

    expect($proposal->fresh()->status)->toBe(MedicationPlanProposalStatus::ACCEPTED);
    expect($family->fresh()->patients()->whereKey($patient->id)->exists())->toBeTrue();
    expect(Medication::query()->where('patient_id', $patient->id)->count())->toBe(1);
});

test('patients cannot accept a plan sent to another email address', function () {
    $wrongPatient = User::factory()->patient()->create([
        'email' => 'wrong-'.uniqid('', true).'@example.com',
        'email_verified_at' => now(),
    ]);

    $family = User::factory()->familyMember()->create()->familyOrCreate();
    $intendedEmail = 'intended-'.uniqid('', true).'@example.com';

    $proposal = MedicationPlanProposal::factory()
        ->forFamily($family)
        ->withMedicationItem()
        ->published()
        ->create([
            'invited_patient_email' => $intendedEmail,
            'invited_patient_email_hash' => User::hashEmail($intendedEmail),
        ]);

    $this->actingAs($wrongPatient)
        ->post(route('patient.medication-plans.accept', $proposal))
        ->assertForbidden();
});

test('patients can decline a published plan from the family page', function () {
    $patientEmail = 'plan-decline-'.uniqid('', true).'@example.com';
    $patientUser = User::factory()->patient()->create([
        'email' => $patientEmail,
        'email_verified_at' => now(),
    ]);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $family = User::factory()->familyMember()->create()->familyOrCreate();

    $proposal = MedicationPlanProposal::factory()
        ->forFamily($family)
        ->withMedicationItem()
        ->published()
        ->create([
            'invited_patient_email' => $patientEmail,
            'invited_patient_email_hash' => User::hashEmail($patientEmail),
        ]);

    $this->actingAs($patientUser)
        ->post(route('patient.medication-plans.decline', $proposal))
        ->assertRedirect(route('patient.family'));

    expect($proposal->fresh()->status)->toBe(MedicationPlanProposalStatus::DECLINED);
    expect(Medication::query()->where('patient_id', $patient->id)->count())->toBe(0);
});

/**
 * @return array<string, mixed>
 */
/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function validMedicationPlanProposalPayload(array $overrides = []): array
{
    return [
        ...[
            'name' => 'Ibuprofen',
            'dose' => '1',
            'dose_unit' => 'piece',
            'type_medication' => 'pill',
            'strength' => '400 mg',
            'note' => null,
            'current_stock' => '24',
            'schedule' => [
                'meal_timing' => 'after_food',
                'intake_frequency' => 'daily',
                'intake_weekdays' => null,
                'times_per_day' => '1',
                'dose_quantity' => '1',
                'dose_time' => '09:00',
                'snooze_time' => '30',
                'start_date' => now()->toDateString(),
                'end_date' => null,
            ],
        ],
        ...$overrides,
    ];
}
