<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_patient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['family_id', 'patient_id']);
        });

        Schema::table('families', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropColumn('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_patient');

        Schema::table('families', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }
};
