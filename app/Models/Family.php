<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Family extends Model
{
    use HasFactory;

    /**************************************/
    /*             Attributes */
    /**************************************/

    protected $fillable = [
        'user_id',
    ];

    /**************************************/
    /*           Relationships */
    /**************************************/

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'family_patient')
            ->withTimestamps();
    }

    public function hasLinkedPatient(): bool
    {
        return $this->patients()->exists();
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
}
