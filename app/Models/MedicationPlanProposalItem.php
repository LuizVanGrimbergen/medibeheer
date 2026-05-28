<?php

namespace App\Models;

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MedicationPlanProposalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'medication_plan_proposal_id',
        'sort_order',
        'name',
        'dose',
        'dose_unit',
        'type_medication',
        'strength',
        'note',
        'current_stock',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'name' => 'encrypted',
            'dose' => 'encrypted',
            'dose_unit' => MedicationDoseUnit::class,
            'type_medication' => MedicationType::class,
            'strength' => 'encrypted',
            'note' => 'encrypted',
            'current_stock' => 'encrypted',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(MedicationPlanProposal::class, 'medication_plan_proposal_id');
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(MedicationPlanProposalItemSchedule::class);
    }
}
