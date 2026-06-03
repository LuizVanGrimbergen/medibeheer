<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->date('checkin_date');
            $table->text('mood_score');
            $table->text('note')->nullable();
            $table->text('encouragement_message')->nullable();
            $table->timestamps();

            $table->unique(['patient_id', 'checkin_date']);
            $table->index(['patient_id', 'checkin_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_checkins');
    }
};
