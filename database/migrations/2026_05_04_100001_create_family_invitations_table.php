<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->text('invited_email');
            $table->string('invited_email_hash');
            $table->string('token_hash', 64);
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique('token_hash');
            $table->index(['patient_id', 'invited_email_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_invitations');
    }
};
