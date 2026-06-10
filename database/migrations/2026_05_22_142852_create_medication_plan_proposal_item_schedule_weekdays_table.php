<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_plan_proposal_item_schedule_weekdays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_plan_proposal_item_schedule_id')->constrained('medication_plan_proposal_item_schedules', 'id', 'mpp_item_sched_wd_sched_id_fk')->cascadeOnDelete();
            $table->text('weekday');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_plan_proposal_item_schedule_weekdays');
    }
};
