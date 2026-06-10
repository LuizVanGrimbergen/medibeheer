<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->date('prescription_expiry_date')->nullable();
            $table->boolean('is_last_in_batch')->default(false);
            $table->text('pickup_status');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['medication_id', 'deleted_at']);
            $table->index(['medication_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_prescriptions');
    }
};
