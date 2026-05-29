<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use App\Models\Family;
use App\Models\Patient;
use App\Services\Appointments\AppointmentTransportInvitationService;
use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(?Patient $patient = null): void
    {
        $transportService = app(AppointmentTransportInvitationService::class);
        $f = fake('nl_NL');

        $query = Patient::query()->with(['families.user']);

        if ($patient !== null) {
            $query->whereKey($patient->getKey());
        }

        if ($query->clone()->doesntExist()) {
            if ($this->command !== null) {
                $this->command->warn('AppointmentSeeder skipped: no patients in the database.');
            }

            return;
        }

        $created = 0;

        $query->each(function (Patient $patientModel) use ($transportService, $f, &$created): void {
            $created += $this->seedAppointmentsForPatient($patientModel, $transportService, $f);
        });

        if ($this->command !== null) {
            $this->command->info("AppointmentSeeder finished: {$created} appointments created.");
        }
    }

    private function seedAppointmentsForPatient(
        Patient $patient,
        AppointmentTransportInvitationService $transportService,
        Generator $f,
    ): int {
        $patient->loadMissing('families.user');

        $linkedFamilyIds = $patient->families->pluck('id')->all();
        $familyDriverFirstName = $this->familyContactFirstName($patient);
        $created = 0;

        $allFamilyIdsInt = array_values(array_map(
            static fn (int|string $id): int => (int) $id,
            $linkedFamilyIds,
        ));

        $plannedCount = $allFamilyIdsInt !== []
            ? max($f->numberBetween(5, 9), 5)
            : $f->numberBetween(5, 9);

        foreach (range(1, $plannedCount) as $index) {
            $doctorType = $f->randomElement(DoctorType::cases());
            $status = AppointmentStatus::SCHEDULED;

            $transportInviteFamilyIds = null;
            $transportOutcome = null;
            $needsTransport = false;

            $demoTransportSlot = $allFamilyIdsInt !== [] && $index <= 4;

            if ($demoTransportSlot) {
                $startsAt = Carbon::today()
                    ->addDays(10 + ($index * 7))
                    ->setTime(9 + min($index, 4), 15, 0);
            } else {
                $startsAt = $this->randomAppointmentStartsAtBetween($f, '+2 days', '+4 months');
            }

            if ($allFamilyIdsInt !== []) {
                if ($index === 1) {
                    $needsTransport = true;
                    $transportInviteFamilyIds = $allFamilyIdsInt;
                    $transportOutcome = 'pending';
                } elseif ($index === 2) {
                    $needsTransport = true;
                    $transportInviteFamilyIds = $allFamilyIdsInt;
                    $transportOutcome = 'accepted';
                } elseif ($index === 3) {
                    $needsTransport = true;
                    $transportInviteFamilyIds = $allFamilyIdsInt;
                    $transportOutcome = 'declined';
                } elseif ($index === 4) {
                    $needsTransport = true;
                    $transportInviteFamilyIds = $allFamilyIdsInt;
                    $transportOutcome = 'pending';
                } else {
                    $needsTransport = $f->boolean(45);
                }
            }

            $created += $this->createSeededAppointment(
                $patient,
                $transportService,
                $f,
                $doctorType,
                $startsAt,
                $status,
                $needsTransport,
                $linkedFamilyIds,
                $familyDriverFirstName,
                $transportInviteFamilyIds,
                $transportOutcome,
            );
        }

        $historyCount = $f->numberBetween(4, 10);
        foreach (range(1, $historyCount) as $_) {
            $doctorType = $f->randomElement(DoctorType::cases());
            $startsAt = $this->randomAppointmentStartsAtBetween($f, '-6 months', '-1 day');
            $status = $this->statusForPastAppointment($f);

            $created += $this->createSeededAppointment(
                $patient,
                $transportService,
                $f,
                $doctorType,
                $startsAt,
                $status,
                false,
                $linkedFamilyIds,
                $familyDriverFirstName,
                null,
                null,
            );
        }

        return $created;
    }

    /**
     * @param  list<int|string>  $linkedFamilyIds
     * @param  list<int>|null  $transportInviteFamilyIds
     * @param  'pending'|'accepted'|'declined'|null  $transportOutcome
     */
    private function createSeededAppointment(
        Patient $patient,
        AppointmentTransportInvitationService $transportService,
        Generator $f,
        DoctorType $doctorType,
        Carbon $startsAt,
        AppointmentStatus $status,
        bool $needsTransport,
        array $linkedFamilyIds,
        ?string $familyDriverFirstName,
        ?array $transportInviteFamilyIds,
        ?string $transportOutcome = null,
    ): int {
        $attributes = array_merge(
            $this->dutchLocationForDoctorType($doctorType, $f),
            [
                'status' => $status,
                'starts_at' => $startsAt,
                'needs_transport' => $needsTransport,
                'doctor_type' => $doctorType,
                'notes' => $this->randomAppointmentNote($f, $familyDriverFirstName, $needsTransport),
                'doctor_visit_summary' => $status === AppointmentStatus::DONE
                    ? $f->optional(0.55)->randomElement([
                        'Stabiele waarden, controle over zes maanden.',
                        'Doorverwijzing naar specialist besproken; verwijsbrief volgt.',
                        'Behandeling afgerond; geen vervolgafspraak nodig.',
                        'Aanpassing medicatie; herhaal recept via huisarts.',
                    ])
                    : null,
                'cancellation_reason' => $status === AppointmentStatus::CANCELLED
                    ? $f->randomElement([
                        'Ziek; afspraak verzet naar volgende week.',
                        'Verhinderd door familieomstandigheden.',
                        'Dubbele afspraak in agenda; graag annuleren.',
                        'Verplaatst naar ander ziekenhuis dicht bij familie.',
                    ])
                    : null,
            ],
        );

        $appointment = Appointment::factory()
            ->for($patient)
            ->create($attributes);

        if ($needsTransport && $linkedFamilyIds !== []) {
            $inviteIds = $transportInviteFamilyIds ?? array_values(array_map(
                static fn (int|string $id): int => (int) $id,
                $f->randomElements(
                    $linkedFamilyIds,
                    $f->numberBetween(1, count($linkedFamilyIds)),
                ),
            ));

            $transportService->syncForAppointment($appointment, $inviteIds);

            $this->applySeededTransportOutcome(
                $transportService,
                $appointment->fresh(),
                $patient,
                $transportOutcome,
            );
        }

        return 1;
    }

    /**
     * @param  'pending'|'accepted'|'declined'|null  $transportOutcome
     */
    private function applySeededTransportOutcome(
        AppointmentTransportInvitationService $transportService,
        Appointment $appointment,
        Patient $patient,
        ?string $transportOutcome,
    ): void {
        if ($transportOutcome === null || $transportOutcome === 'pending') {
            return;
        }

        if ($transportOutcome !== 'accepted' && $transportOutcome !== 'declined') {
            return;
        }

        $family = $patient->families->sortBy('id')->first();

        if (! $family instanceof Family) {
            return;
        }

        $appointment->load('transportInvitations');

        $invitation = $appointment->transportInvitations
            ->firstWhere('family_id', $family->id);

        if ($invitation === null || ! $invitation->isPending()) {
            return;
        }

        if ($transportOutcome === 'accepted') {
            $transportService->accept($invitation, $family);

            return;
        }

        $transportService->decline($invitation, $family);
    }

    private function randomAppointmentStartsAtBetween(Generator $f, string $from, string $to): Carbon
    {
        $startsAt = Carbon::parse($f->dateTimeBetween($from, $to))->seconds(0);

        if ($startsAt->hour < 7) {
            $startsAt->setTime($f->numberBetween(8, 11), $f->randomElement([0, 15, 30]), 0);
        }

        if ($startsAt->hour >= 18) {
            $startsAt->setTime($f->numberBetween(8, 16), $f->randomElement([0, 15, 30]), 0);
        }

        return $startsAt;
    }

    private function statusForPastAppointment(Generator $f): AppointmentStatus
    {
        return $f->boolean(16) ? AppointmentStatus::CANCELLED : AppointmentStatus::DONE;
    }

    /** @return array{provider_name: string, street: string, house_number: string, postal_code: string, city: string} */
    private function dutchLocationForDoctorType(DoctorType $type, Generator $f): array
    {
        $city = $f->city();
        $last = $f->lastName();

        $providerName = match ($type) {
            DoctorType::DENTIST => $f->randomElement([
                "Tandartsenpraktijk {$last}",
                "Mondzorgcentrum {$city}",
                "Tandheelkundig Centrum {$city}",
            ]),
            DoctorType::HOSPITAL => $f->randomElement([
                "{$city} Ziekenhuis",
                "Streekziekenhuis {$city}",
                "Medisch Centrum {$last}",
            ]),
            DoctorType::GENERAL_PRACTITIONER => $f->randomElement([
                "Huisartsenpraktijk {$last} & collega’s",
                "Gezondheidscentrum {$city}",
                "Huisartsenpost {$city}",
            ]),
            DoctorType::SPECIALIST => $f->randomElement([
                "Polikliniek {$f->randomElement(['Cardiologie', 'Dermatologie', 'Reumatologie', 'Oogheelkunde'])} — {$city}",
                "Specialistencentrum {$city}",
                "Kliniek {$last}",
            ]),
        };

        return [
            'provider_name' => $providerName,
            'street' => $f->streetName(),
            'house_number' => (string) $f->buildingNumber(),
            'postal_code' => $f->postcode(),
            'city' => $city,
        ];
    }

    private function randomAppointmentNote(Generator $f, ?string $familyDriverFirstName, bool $needsTransport): ?string
    {
        $pool = [
            'Gelieve een actuele medicijnlijst mee te nemen.',
            'Nuchter blijven vanaf middernacht (onderzoek).',
            'Parkeergarage P2, lift naar route bord 4.',
            'Begeleiding gewenst ivm slecht ter been.',
            'Controle bloeddruk en labuitslagen bespreken.',
        ];

        if ($familyDriverFirstName !== null) {
            $pool[] = "Voorkeur: {$familyDriverFirstName} rijdt mee (parkeerkaart bij balie).";

            if ($needsTransport) {
                $pool[] = "{$familyDriverFirstName} op de hoogte gezet voor ophalen en terugbrengen.";
            }
        }

        return $f->optional(0.36)->randomElement($pool);
    }

    private function familyContactFirstName(Patient $patient): ?string
    {
        $name = $patient->families->first()?->user?->name;

        if ($name === null || $name === '') {
            return null;
        }

        $parts = preg_split('/\s+/', $name, 2);

        return $parts[0] ?? null;
    }
}
