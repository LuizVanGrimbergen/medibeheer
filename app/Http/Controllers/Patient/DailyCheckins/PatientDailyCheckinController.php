<?php

namespace App\Http\Controllers\Patient\DailyCheckins;

use App\Enums\DailyMoodScore;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\StoreDailyCheckinRequest;
use App\Models\DailyCheckin;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PatientDailyCheckinController extends Controller
{
    use AuthorizesPatientProfile;

    public function store(StoreDailyCheckinRequest $request): RedirectResponse
    {
        $patient = $this->authorizePatientProfile($request);

        $this->authorize('create', DailyCheckin::class);

        $validated = $request->validated();

        $today = CarbonImmutable::today();

        $alreadyCheckedInToday = $patient
            ->dailyCheckins()
            ->whereDate('checkin_date', $today->toDateString())
            ->exists();

        if ($alreadyCheckedInToday) {
            return redirect()->route('patient.dashboard');
        }

        $moodRaw = $validated['mood_score'];
        $mood = $moodRaw instanceof DailyMoodScore
            ? $moodRaw
            : DailyMoodScore::from((string) $moodRaw);

        DB::transaction(function () use ($patient, $today, $mood, $validated): void {
            $checkin = $patient->dailyCheckins()->create([
                'checkin_date' => $today->toDateString(),
                'mood_score' => $mood,
                'note' => $validated['note'] ?? null,
            ]);

            if ($mood === DailyMoodScore::BAD || $mood === DailyMoodScore::OK) {
                $values = array_unique(array_values($validated['symptoms'] ?? []));

                foreach ($values as $symptomValue) {
                    $checkin->selectedSymptoms()->create([
                        'symptom' => (string) $symptomValue,
                    ]);
                }
            }
        });

        return redirect()
            ->route('patient.dashboard')
            ->with('daily_checkin_mood', $mood->value);
    }
}
