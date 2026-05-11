<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('doctor_type');
            $table->text('provider_name');
            $table->text('street');
            $table->text('house_number');
            $table->text('postal_code');
            $table->text('city');
            $table->boolean('needs_transport')->default(false);
            $table->dateTime('starts_at');
            $table->text('notes')->nullable();
            $table->string('status')->default('scheduled');
            $table->text('doctor_visit_summary')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
