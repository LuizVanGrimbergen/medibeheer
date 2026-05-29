<?php

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function createLinkedFamilyMemberForPatient(Patient $patient): User
{
    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->familyOrCreate();
    $family->patients()->attach($patient->id);

    return $familyUser;
}

function assertInertiaRootComponent(TestResponse $response, string $component): void
{
    preg_match('/<div[^>]*id="app"[^>]*data-page="([^"]+)"/', $response->getContent(), $matches);

    expect($matches)->not->toBeEmpty('Missing Inertia #app data-page');

    /** @var array<string, mixed> $page */
    $page = json_decode(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5), true);

    expect($page)->toBeArray();
    expect($page['component'] ?? null)->toBe($component);
}
