<?php

namespace Database\Seeders;

use App\Enums\DailyCheckinSymptom;
use App\Enums\DailyMoodScore;
use App\Models\DailyCheckin;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Generator;
use Illuminate\Database\Seeder;

class DailyCheckinDemoSeeder extends Seeder
{
    public function run(?Patient $patient = null): void
    {
        $patient ??= User::findByEmail(DatabaseSeeder::DEMO_PATIENT_EMAIL)?->patient;
        $patient ??= Patient::query()->first();

        if ($patient === null) {
            if ($this->command !== null) {
                $this->command->warn('DailyCheckinDemoSeeder skipped: geen patiënt (run DatabaseSeeder eerst of geef een patiënt mee).');
            }

            return;
        }

        $patient->loadMissing('families.user', 'user');

        $f = fake('nl_NL');

        $start = Carbon::today()->subMonths(6)->startOfMonth();
        $end = Carbon::today();

        $created = 0;

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $crc = crc32($date->format('Y-m-d'));

            if ($crc % 13 === 0) {
                continue;
            }

            $weekday = $date->dayOfWeekIso;

            $mood = match (true) {
                $weekday <= 3 && ($crc % 7) <= 3 => DailyMoodScore::BAD,
                $weekday >= 5 && ($crc % 4) === 0 => DailyMoodScore::GOOD,
                ($crc % 6) <= 2 => DailyMoodScore::GOOD,
                ($crc % 6) <= 4 => DailyMoodScore::OK,
                default => DailyMoodScore::BAD,
            };

            [$symptoms, $note] = $this->symptomsAndNoteForDemoDay($mood, $crc, $f, $patient);

            $checkin = DailyCheckin::query()->updateOrCreate(
                [
                    'patient_id' => $patient->id,
                    'checkin_date' => $date->format('Y-m-d'),
                ],
                [
                    'mood_score' => $mood,
                    'note' => $note,
                ],
            );

            $this->syncCheckinSymptoms($checkin, $symptoms);

            $created++;
        }

        if ($this->command !== null) {
            $familyUser = $patient->families->first()?->user;
            $familyEmail = $familyUser?->email ?? '—';

            $this->command->info(sprintf(
                'Dagelijkse check-ins: %d dagen voor %s (%s); familie-login %s (wachtwoord: password).',
                $created,
                $patient->user->name,
                $patient->user->email,
                $familyEmail,
            ));
        }
    }

    /**
     * @return array{0: list<DailyCheckinSymptom>, 1: ?string}
     */
    private function symptomsAndNoteForDemoDay(
        DailyMoodScore $mood,
        int $crc,
        Generator $f,
        Patient $patient,
    ): array {
        $familyFirstName = $this->familyContactFirstName($patient);

        return match ($mood) {
            DailyMoodScore::GOOD => [
                [],
                $this->noteForGoodDay($crc, $f, $familyFirstName),
            ],
            DailyMoodScore::OK => $this->scenarioForOkDay($crc, $f, $familyFirstName),
            DailyMoodScore::BAD => $this->scenarioForBadDay($crc, $f, $familyFirstName),
        };
    }

    /**
     * @return array{0: list<DailyCheckinSymptom>, 1: ?string}
     */
    private function scenarioForBadDay(int $crc, Generator $f, ?string $familyFirstName): array
    {
        $scenarios = $this->badDayScenarios($familyFirstName);
        $scenario = $scenarios[$this->scenarioIndex($crc, count($scenarios))];

        return [
            $scenario['symptoms'],
            $this->maybePickScenarioNote($crc, $f, $scenario['notes']),
        ];
    }

    /**
     * @return array{0: list<DailyCheckinSymptom>, 1: ?string}
     */
    private function scenarioForOkDay(int $crc, Generator $f, ?string $familyFirstName): array
    {
        $scenarios = $this->okDayScenarios($familyFirstName);
        $scenario = $scenarios[$this->scenarioIndex($crc, count($scenarios))];

        return [
            $scenario['symptoms'],
            $this->maybePickScenarioNote($crc, $f, $scenario['notes']),
        ];
    }

    /**
     * @param  list<string>  $notes
     */
    private function maybePickScenarioNote(int $crc, Generator $f, array $notes): ?string
    {
        if ($notes === []) {
            return null;
        }

        if ($crc % 11 === 0) {
            return null;
        }

        return $f->randomElement($notes);
    }

    private function noteForGoodDay(int $crc, Generator $f, ?string $familyFirstName): ?string
    {
        if ($crc % 5 === 0) {
            return null;
        }

        $pool = [
            'Lekker geslapen en goed gegeten; energie voelt normaal.',
            'Rustige dag; bloeddruk thuis gemeten en binnen de gewenste range.',
            'Lichte wandeling gemaakt; geen bijzondere klachten achteraf.',
            'Bezoek van de kleinkinderen; dat gaf een fijne afleiding.',
            'Medicatie goed ingenomen; geen bijwerkingen gemerkt vandaag.',
            'Goed kunnen concentreren bij lezen en televisie.',
        ];

        if ($familyFirstName !== null) {
            $pool[] = "{$familyFirstName} langs geweest; gezellig en rustgevend.";
            $pool[] = "Samen met {$familyFirstName} een kop koffie gedronken; voelde me daarna opgewekt.";
            $pool[] = "{$familyFirstName} gebeld; even bijgepraat en daarna merkbaar lichter gevoel.";
        }

        return $f->randomElement($pool);
    }

    /**
     * @return list<array{symptoms: list<DailyCheckinSymptom>, notes: list<string>}>
     */
    private function badDayScenarios(?string $familyFirstName): array
    {
        $scenarios = [
            [
                'symptoms' => [
                    DailyCheckinSymptom::SHORTNESS_OF_BREATH,
                    DailyCheckinSymptom::FATIGUE,
                    DailyCheckinSymptom::POOR_SLEEP,
                ],
                'notes' => [
                    'Vannacht wakker geworden van benauwdheid; inhalator geeft wat verlichting maar niet lang genoeg.',
                    'Na een korte trap merkbaar kortademig; moet even zitten om weer rustig te ademen.',
                    'Lucht voelt “zwaar”; bij inspanning sneller opgeblazen dan normaal.',
                ],
            ],
            [
                'symptoms' => [
                    DailyCheckinSymptom::PAIN,
                    DailyCheckinSymptom::STIFF_OR_JOINT_PAIN,
                    DailyCheckinSymptom::FATIGUE,
                ],
                'notes' => [
                    'Gewrichten stijf bij opstaan; pijnstillers rond de lunch geholpen maar niet helemaal weg.',
                    'Rug en heup doen extra zeer; draaien in bed ging moeizaam.',
                    'Spierpijn na licht huishouden; meer rust genomen dan gepland.',
                ],
            ],
            [
                'symptoms' => [
                    DailyCheckinSymptom::NAUSEA,
                    DailyCheckinSymptom::POOR_APPETITE,
                    DailyCheckinSymptom::DIZZINESS,
                ],
                'notes' => [
                    'Weinig trek gehad; bij staan even duizelig geweest. Vocht wel blijven drinken.',
                    'Misselijk gevoel na het ontbijt; middag iets kunnen eten zonder over te geven.',
                    'Draaierig bij snel opstaan; bewegingen rustiger gedaan vandaag.',
                ],
            ],
            [
                'symptoms' => [
                    DailyCheckinSymptom::LONELINESS,
                    DailyCheckinSymptom::ANXIETY_OR_WORRY,
                    DailyCheckinSymptom::POOR_SLEEP,
                ],
                'notes' => [
                    'Veel piekeren over het komende ziekenhuisbezoek; slecht ingeslapen.',
                    'Dag voelde lang en leeg; weinig praatjes gehad en dat vond ik zwaar.',
                    'Onrust in het lijf; moeilijk tot rust komen ondanks warme drank voor het slapen.',
                ],
            ],
            [
                'symptoms' => [
                    DailyCheckinSymptom::DIZZINESS,
                    DailyCheckinSymptom::FATIGUE,
                ],
                'notes' => [
                    'Even wankelig bij het aanreiken van bovenkast; daarna rustig aan gedaan.',
                    'Licht in het hoofd na medicatie; even gezeten tot het wegtrok.',
                ],
            ],
            [
                'symptoms' => [
                    DailyCheckinSymptom::SHORTNESS_OF_BREATH,
                    DailyCheckinSymptom::ANXIETY_OR_WORRY,
                ],
                'notes' => [
                    'Benauwd gevoel kwam op tijdens spanning; na rust langzaam minder.',
                    'Ademhaling oppervlakkig toen ik me zorgen maakte; kalmeren hielp een beetje.',
                ],
            ],
            [
                'symptoms' => [
                    DailyCheckinSymptom::PAIN,
                    DailyCheckinSymptom::POOR_SLEEP,
                ],
                'notes' => [
                    'Pijn hield me wakker; pas laat ingedommeld en vandaag extra moe.',
                    'Nachtmerrie en daarna pijnlijke schouder; overdag warme kruik gebruikt.',
                ],
            ],
            [
                'symptoms' => [
                    DailyCheckinSymptom::FATIGUE,
                    DailyCheckinSymptom::STIFF_OR_JOINT_PAIN,
                    DailyCheckinSymptom::POOR_APPETITE,
                ],
                'notes' => [
                    'Futloos en weinig zin in eten; wel soep gehaald dat beter ging.',
                    'Lichaam voelt zwaar; gewrichten klikken en pijn bij de eerste stappen.',
                ],
            ],
        ];

        if ($familyFirstName !== null) {
            $scenarios[] = [
                'symptoms' => [
                    DailyCheckinSymptom::LONELINESS,
                    DailyCheckinSymptom::ANXIETY_OR_WORRY,
                ],
                'notes' => [
                    "Extra eenzaam; {$familyFirstName} kon pas laat bellen, toen ging het iets beter.",
                    "Wachten op bezoek van {$familyFirstName}; onrust maakte de dag zwaar.",
                ],
            ];
        }

        return $scenarios;
    }

    /**
     * @return list<array{symptoms: list<DailyCheckinSymptom>, notes: list<string>}>
     */
    private function okDayScenarios(?string $familyFirstName): array
    {
        $scenarios = [
            [
                'symptoms' => [
                    DailyCheckinSymptom::FATIGUE,
                    DailyCheckinSymptom::POOR_SLEEP,
                ],
                'notes' => [
                    'Niet slecht, maar wél moe; gisteravond slecht geslapen door warmte op de kamer.',
                    'Overdag redelijk; tegen de avond merkbaar dat de nacht te kort was.',
                ],
            ],
            [
                'symptoms' => [
                    DailyCheckinSymptom::PAIN,
                ],
                'notes' => [
                    'Rug zit weer een beetje klem; verder redelijk mobiel gebleven.',
                    'Lichte hoofdpijn aan het eind van de middag; water gedronken en rust genomen.',
                ],
            ],
            [
                'symptoms' => [
                    DailyCheckinSymptom::ANXIETY_OR_WORRY,
                ],
                'notes' => [
                    'Wachten op uitslag van bloedonderzoek maakt me onrustig, lichamelijk gaat het verder wel.',
                    'Wat gespannen voor morgen; vandaag wel normaal kunnen eten en bewegen.',
                ],
            ],
            [
                'symptoms' => [
                    DailyCheckinSymptom::STIFF_OR_JOINT_PAIN,
                    DailyCheckinSymptom::FATIGUE,
                ],
                'notes' => [
                    'Stijf bij opstaan; na douche en bewegen iets soepeler geworden.',
                    'Gewrichten voelen “oud” vandaag; geen erge pieken maar constant irritant.',
                ],
            ],
            [
                'symptoms' => [],
                'notes' => [
                    'Geen duidelijke lichamelijke klachten; beetje futloos maar verder stabiel.',
                    'Vandaag geen extra symptomen opgemerkt; humeur wisselend maar acceptabel.',
                ],
            ],
            [
                'symptoms' => [
                    DailyCheckinSymptom::POOR_APPETITE,
                ],
                'notes' => [
                    'Minder trek dan normaal; wel geprobeerd kleine porties te eten.',
                ],
            ],
        ];

        if ($familyFirstName !== null) {
            $scenarios[] = [
                'symptoms' => [
                    DailyCheckinSymptom::FATIGUE,
                ],
                'notes' => [
                    "Rustige dag; {$familyFirstName} hielp met boodschappen, scheelde veel energie.",
                ],
            ];
        }

        return $scenarios;
    }

    private function scenarioIndex(int $crc, int $count): int
    {
        if ($count <= 0) {
            return 0;
        }

        $index = $crc % $count;

        return $index >= 0 ? $index : $index + $count;
    }

    /**
     * @param  list<DailyCheckinSymptom>  $symptoms
     */
    private function syncCheckinSymptoms(DailyCheckin $checkin, array $symptoms): void
    {
        $checkin->selectedSymptoms()->delete();

        foreach ($symptoms as $symptom) {
            $checkin->selectedSymptoms()->create([
                'symptom' => $symptom,
            ]);
        }
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
