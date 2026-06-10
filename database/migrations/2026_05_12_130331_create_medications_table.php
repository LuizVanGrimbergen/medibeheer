<?php

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
            $table->text('name');
            $table->text('dose');
            $table->text('dose_unit');
            $table->text('strength')->nullable();
            $table->text('type_medication');
            $table->text('note')->nullable();
            $table->text('stock_pieces_per_package')->nullable();
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
