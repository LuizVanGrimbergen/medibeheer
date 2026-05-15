<?php

use App\Enums\MedicationDoseUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('name');
            $table->text('dose');
            $table->string('dose_unit', 32)->default(MedicationDoseUnit::OTHER->value);
            $table->text('type_medication');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
