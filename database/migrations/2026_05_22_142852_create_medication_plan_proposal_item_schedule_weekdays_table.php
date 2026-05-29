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
            $table->foreignId('medication_plan_proposal_item_schedule_id');
            $table->foreign('medication_plan_proposal_item_schedule_id', 'mpp_item_schedule_wd_schedule_id_foreign')
                ->references('id')
                ->on('medication_plan_proposal_item_schedules')
                ->cascadeOnDelete();
            $table->text('weekday');
            $table->timestamps();

            $table->index('medication_plan_proposal_item_schedule_id', 'mpp_item_schedule_wd_schedule_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_plan_proposal_item_schedule_weekdays');
    }
};
