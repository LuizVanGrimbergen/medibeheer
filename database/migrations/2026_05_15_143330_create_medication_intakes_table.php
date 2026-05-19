<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_intakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medication_schedule_id')->constrained()->cascadeOnDelete();
            $table->date('intake_date');
            $table->text('dose_time');
            $table->char('dose_time_index', 64);
            $table->timestamp('taken_at');
            $table->timestamps();

            $table->unique(
                ['medication_schedule_id', 'intake_date', 'dose_time_index'],
                'medication_intakes_schedule_date_dose_unique',
            );
            $table->index(['patient_id', 'intake_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_intakes');
    }
};
