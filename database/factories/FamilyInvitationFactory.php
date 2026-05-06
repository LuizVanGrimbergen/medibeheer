<?php

namespace Database\Factories;

use App\Models\FamilyInvitation;
use App\Models\Patient;
use App\Models\User;
use App\Services\FamilyInvitationService;
use Illuminate\Database\Eloquent\Factories\Factory;

class FamilyInvitationFactory extends Factory
{
    protected $model = FamilyInvitation::class;

    public function definition(): array
    {
        $email = fake()->unique()->safeEmail();
        $normalized = User::normalizeEmail($email);
        $plainToken = bin2hex(random_bytes(20));

        return [
            'patient_id' => Patient::factory(),
            'invited_email' => $normalized,
            'invited_email_hash' => User::hashEmail($normalized),
            'token_hash' => hash('sha256', FamilyInvitationService::normalizeInviteCode($plainToken)),
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
            'revoked_at' => null,
        ];
    }

    public function withPlainToken(string $plainToken): static
    {
        return $this->state(fn (array $attributes): array => [
            'token_hash' => hash('sha256', FamilyInvitationService::normalizeInviteCode($plainToken)),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes): array => [
            'revoked_at' => now(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'accepted_at' => now(),
        ]);
    }
}
