<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medication_plan_proposals', function (Blueprint $table): void {
            $table->text('invited_patient_email')->nullable()->after('family_id');
            $table->string('invited_patient_email_hash', 64)->nullable()->after('invited_patient_email');
            $table->index('invited_patient_email_hash');
        });
    }

    public function down(): void
    {
        Schema::table('medication_plan_proposals', function (Blueprint $table): void {
            $table->dropIndex(['invited_patient_email_hash']);
            $table->dropColumn(['invited_patient_email', 'invited_patient_email_hash']);
        });
    }
};
