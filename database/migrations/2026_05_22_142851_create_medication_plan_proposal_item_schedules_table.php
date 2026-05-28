<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_plan_proposal_item_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_plan_proposal_item_id');
            $table->foreign('medication_plan_proposal_item_id', 'mpp_item_schedules_item_id_foreign')
                ->references('id')
                ->on('medication_plan_proposal_items')
                ->cascadeOnDelete();
            $table->text('meal_timing');
            $table->text('intake_frequency');
            $table->text('times_per_day');
            $table->text('dose_quantity');
            $table->text('dose_time');
            $table->text('snooze_time');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->unique('medication_plan_proposal_item_id', 'mpp_item_schedules_item_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_plan_proposal_item_schedules');
    }
};
