<?php

namespace Database\Factories;

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Enums\MedicationPlanProposalStatus;
use App\Enums\MedicationType;
use App\Models\Family;
use App\Models\MedicationPlanProposal;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicationPlanProposal>
 */
class MedicationPlanProposalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => null,
            'family_id' => Family::factory(),
            'status' => MedicationPlanProposalStatus::DRAFT,
            'token_hash' => null,
            'expires_at' => null,
            'published_at' => null,
            'accepted_at' => null,
            'declined_at' => null,
            'revoked_at' => null,
        ];
    }

    public function withMedicationItem(): static
    {
        return $this->afterCreating(function (MedicationPlanProposal $proposal): void {
            if ($proposal->items()->exists()) {
                return;
            }

            $item = $proposal->items()->create([
                'sort_order' => 0,
                'name' => 'Paracetamol',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::PIECE,
                'type_medication' => MedicationType::PILL,
                'strength' => '500 mg',
                'note' => null,
                'current_stock' => '30',
            ]);

            $schedule = $item->schedule()->create([
                'meal_timing' => MedicationMealTiming::AFTER_FOOD,
                'intake_frequency' => MedicationIntakeFrequency::DAILY,
                'times_per_day' => '1',
                'dose_quantity' => '1',
                'dose_time' => '08:00',
                'snooze_time' => '30',
                'start_date' => now()->toDateString(),
                'end_date' => null,
            ]);

            $schedule->syncIntakeWeekdays(null);
        });
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => MedicationPlanProposalStatus::PUBLISHED,
            'token_hash' => hash('sha256', bin2hex(random_bytes(20))),
            'expires_at' => now()->addDays(14),
            'published_at' => now(),
        ]);
    }

    public function forFamily(Family $family): static
    {
        return $this->state(fn (): array => [
            'family_id' => $family->id,
        ]);
    }

    public function forPatient(Patient $patient): static
    {
        return $this->state(fn (): array => [
            'patient_id' => $patient->id,
        ]);
    }
}
