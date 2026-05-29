<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_schedule_weekdays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_schedule_id')
                ->constrained('medication_schedules')
                ->cascadeOnDelete();
            $table->text('weekday');
            $table->timestamps();

            $table->index('medication_schedule_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_schedule_weekdays');
    }
};
