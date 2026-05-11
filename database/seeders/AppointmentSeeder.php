<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use App\Models\Patient;
use App\Services\AppointmentTransportInvitationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $transportService = app(AppointmentTransportInvitationService::class);

        $patientCount = Patient::query()->count();

        if ($patientCount === 0) {
            if ($this->command !== null) {
                $this->command->warn('AppointmentSeeder skipped: no patients in the database.');
            }

            return;
        }

        $created = 0;

        Patient::query()->with('families')->each(function (Patient $patient) use ($transportService, &$created): void {
            $linkedFamilyIds = $patient->families->pluck('id')->all();

            foreach (range(1, fake()->numberBetween(2, 5)) as $_) {
                $needsTransport = $linkedFamilyIds !== [] && fake()->boolean(35);

                $appointment = Appointment::factory()
                    ->for($patient)
                    ->create([
                        'status' => AppointmentStatus::SCHEDULED,
                        'starts_at' => Carbon::now()->subDays(fake()->numberBetween(0, 56)),
                        'needs_transport' => $needsTransport,
                        'doctor_type' => fake()->randomElement(DoctorType::cases()),
                    ]);

                if ($needsTransport) {
                    $inviteIds = fake()->randomElements(
                        $linkedFamilyIds,
                        fake()->numberBetween(1, count($linkedFamilyIds)),
                    );

                    $transportService->syncForAppointment(
                        $appointment,
                        array_values(array_map(
                            static fn (int|string $id): int => (int) $id,
                            $inviteIds,
                        )),
                    );
                }

                $created++;
            }
        });

        if ($this->command !== null) {
            $this->command->info("AppointmentSeeder finished: {$created} scheduled appointments created.");
        }
    }
}
