<?php

use App\Enums\MedicationDoseUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_plan_proposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_plan_proposal_id');
            $table->foreign('medication_plan_proposal_id', 'mpp_items_proposal_id_foreign')
                ->references('id')
                ->on('medication_plan_proposals')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('name');
            $table->text('dose');
            $table->string('dose_unit', 32)->default(MedicationDoseUnit::OTHER->value);
            $table->text('type_medication');
            $table->text('strength')->nullable();
            $table->text('note')->nullable();
            $table->text('current_stock');
            $table->timestamps();

            $table->index(['medication_plan_proposal_id', 'sort_order'], 'mpp_items_proposal_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_plan_proposal_items');
    }
};
