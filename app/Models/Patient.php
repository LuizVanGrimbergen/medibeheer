<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;
    /**************************************/
    /*             Attributes */
    /**************************************/

    protected $fillable = [
        'user_id',
        'streak_count',
    ];

    protected function casts(): array
    {
        return [
            'streak_count' => 'integer',
        ];
    }

    /**************************************/
    /*           Relationships */
    /**************************************/

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function dailyCheckins(): HasMany
    {
        return $this->hasMany(DailyCheckin::class);
    }

    public function familyInvitations(): HasMany
    {
        return $this->hasMany(FamilyInvitation::class);
    }

    public function families(): BelongsToMany
    {
        return $this->belongsToMany(Family::class, 'family_patient')
            ->withTimestamps();
    }

    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class);
    }

    /**************************************/
    /*       Accessors / Mutators */
    /**************************************/

    /**************************************/
    /*              Scopes */
    /**************************************/

    /**************************************/
    /*              Helpers */
    /**************************************/

    public function defaultMedicationFamilyId(): ?int
    {
        $id = $this->families()->orderBy('families.id')->value('families.id');

        return $id !== null ? (int) $id : null;
    }
}
