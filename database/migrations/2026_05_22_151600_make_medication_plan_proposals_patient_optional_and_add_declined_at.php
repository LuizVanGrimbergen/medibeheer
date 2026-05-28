<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medication_plan_proposals', function (Blueprint $table): void {
            $table->dropForeign(['patient_id']);
            $table->unsignedBigInteger('patient_id')->nullable()->change();
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->cascadeOnDelete();
            $table->timestamp('declined_at')->nullable()->after('accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('medication_plan_proposals', function (Blueprint $table): void {
            $table->dropForeign(['patient_id']);
            $table->dropColumn('declined_at');
            $table->unsignedBigInteger('patient_id')->nullable(false)->change();
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->cascadeOnDelete();
        });
    }
};
