<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_plan_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->text('invited_patient_email')->nullable();
            $table->string('invited_patient_email_hash', 64)->nullable();
            $table->text('status');
            $table->char('status_index', 64);
            $table->string('token_hash', 64)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique('token_hash');
            $table->index(['patient_id', 'family_id', 'status_index'], 'med_plan_prop_patient_family_status_idx');
            $table->index('invited_patient_email_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_plan_proposals');
    }
};
