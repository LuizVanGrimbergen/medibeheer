<?php

use App\Enums\SecurityActivityDescription;
use App\Enums\UserConsentType;
use App\Models\User;
use App\Models\UserConsent;
use App\Services\Audit\ActivityLogName;
use App\Services\Audit\SecurityActivityLogger;
use App\Services\Privacy\UserDataErasureService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/** @return array<string, mixed> */
function validRegistrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'patient',
        'password' => 'Qw7!mR2#xP9@tL4$',
        'password_confirmation' => 'Qw7!mR2#xP9@tL4$',
        'accepted_privacy_policy' => true,
        'accepted_health_data_processing' => true,
    ], $overrides);
}

test('privacy and cookie policy pages can be rendered', function () {
    $contactEmail = config('mail.from.address');

    $this->get(route('legal.privacy'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Legal/Privacy')
            ->has('contactEmail')
            ->where('contactEmail', $contactEmail)
            ->has('retention'));

    $this->get(route('legal.cookies'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Legal/Cookies'));
});

test('registration stores user consents', function () {
    $this->post('/register', validRegistrationPayload([
        'email' => 'consent@example.com',
    ]));

    /** @var User $user */
    $user = auth()->user();

    expect(UserConsent::query()->where('user_id', $user->id)->count())->toBe(2);
    expect(
        UserConsent::query()
            ->where('user_id', $user->id)
            ->where('type', UserConsentType::PRIVACY_POLICY)
            ->exists(),
    )->toBeTrue();
});

test('registration requires privacy consents', function () {
    $response = $this->post('/register', validRegistrationPayload([
        'accepted_privacy_policy' => false,
        'accepted_health_data_processing' => false,
    ]));

    $response
        ->assertSessionHasErrors(['accepted_privacy_policy', 'accepted_health_data_processing']);
    $this->assertGuest();
});

test('authenticated user can export their data', function () {
    /** @var User $user */
    $user = User::factory()->patient()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('settings.export'));

    $response
        ->assertSuccessful()
        ->assertHeader('content-disposition');

    expect($response->headers->get('content-type'))->toContain('application/json');
});

test('account deletion removes user related audit and session records', function () {
    /** @var User $user */
    $user = User::factory()->patient()->create();

    app(SecurityActivityLogger::class)->record(
        SecurityActivityDescription::AUTH_LOGIN_SUCCEEDED,
        causer: $user,
        subject: $user,
    );

    DB::table('sessions')->insert([
        'id' => 'test-session-id',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'payload',
        'last_activity' => time(),
    ]);

    app(UserDataErasureService::class)->eraseUserRelatedRecords($user);
    $user->delete();

    expect(Activity::query()->where('causer_id', $user->id)->exists())->toBeFalse();
    expect(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse();
    expect(User::query()->find($user->id))->toBeNull();
});

test('privacy purge command removes expired activity logs and sessions', function () {
    config([
        'privacy.retention.security_activity_log_days' => 30,
        'privacy.retention.session_days' => 30,
    ]);

    Activity::query()->create([
        'log_name' => ActivityLogName::SECURITY,
        'description' => 'expired',
        'created_at' => Carbon::now()->subDays(40),
        'updated_at' => Carbon::now()->subDays(40),
    ]);

    DB::table('sessions')->insert([
        'id' => 'expired-session',
        'user_id' => null,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'payload',
        'last_activity' => Carbon::now()->subDays(40)->getTimestamp(),
    ]);

    $this->artisan('privacy:purge-expired-data')->assertSuccessful();

    expect(Activity::query()->where('description', 'expired')->exists())->toBeFalse();
    expect(DB::table('sessions')->where('id', 'expired-session')->exists())->toBeFalse();
});
