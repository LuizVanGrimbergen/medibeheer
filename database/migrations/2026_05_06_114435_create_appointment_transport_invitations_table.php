<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_transport_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->timestamp('invited_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['appointment_id', 'family_id'], 'ati_appt_family_uq');
            $table->index(['family_id', 'accepted_at', 'declined_at', 'cancelled_at'], 'ati_family_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_transport_invitations');
    }
};
