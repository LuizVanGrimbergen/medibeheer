<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->text('meal_timing');
            $table->text('intake_frequency');
            $table->json('intake_weekdays')->nullable();
            $table->text('times_per_day');
            $table->text('dose_quantity');
            $table->text('dose_time');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['medication_id', 'deleted_at']);
            $table->index(['patient_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_schedules');
    }
};
