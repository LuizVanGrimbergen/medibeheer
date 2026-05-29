<?php

namespace Database\Factories;

use App\Models\Family;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Family>
 */
class FamilyFactory extends Factory
{
    public function definition(): array
    {
        $user = User::factory()->familyMember()->createQuietly();

        return [
            'user_id' => $user->id,
        ];
    }
}
