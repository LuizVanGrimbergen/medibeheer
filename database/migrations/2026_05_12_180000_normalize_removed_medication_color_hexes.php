<?php

use App\Enums\MedicationColor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('medications')) {
            return;
        }

        $blue = MedicationColor::BLUE->value;
        $legacy = ['#fafafa', '#ca8a04', '#2563eb'];

        foreach ($legacy as $hex) {
            DB::table('medications')->where('color', $hex)->update(['color' => $blue]);
        }
    }
};
