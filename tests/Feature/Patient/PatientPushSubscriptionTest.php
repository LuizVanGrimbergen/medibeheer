<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('patients can store a web push subscription', function () {
    $user = User::factory()->patient()->create();

    $csrfToken = 'test-csrf-token';

    $this->actingAs($user)
        ->withSession(['_token' => $csrfToken])
        ->postJson(route('patient.push-subscriptions.store'), [
            '_token' => $csrfToken,
            'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/demo-subscription-patient',
            'keys' => [
                'p256dh' => 'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
                'auth' => 'tBHItJI5svbpez7KI4CCXg',
            ],
            'contentEncoding' => 'aesgcm',
        ])
        ->assertOk()
        ->assertJson(['stored' => true]);

    expect(
        DB::table('push_subscriptions')
            ->where('subscribable_type', User::class)
            ->where('subscribable_id', $user->id)
            ->where('endpoint', 'https://updates.push.services.mozilla.com/wpush/v2/demo-subscription-patient')
            ->exists(),
    )->toBeTrue();
});

test('guests cannot store a web push subscription', function () {
    $this->postJson(route('patient.push-subscriptions.store'), [
        'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/demo-subscription-guest',
        'keys' => [
            'p256dh' => 'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
            'auth' => 'tBHItJI5svbpez7KI4CCXg',
        ],
    ])->assertUnauthorized();
});

test('patients can destroy all web push subscriptions', function () {
    $user = User::factory()->patient()->create();

    $user->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-subscription-patient-a',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    $user->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-subscription-patient-b',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    $csrfToken = 'test-csrf-token';

    $this->actingAs($user)
        ->withSession(['_token' => $csrfToken])
        ->deleteJson(route('patient.push-subscriptions.destroy'), [
            '_token' => $csrfToken,
        ])
        ->assertOk()
        ->assertJson(['destroyed' => true]);

    expect(
        DB::table('push_subscriptions')
            ->where('subscribable_type', User::class)
            ->where('subscribable_id', $user->id)
            ->exists(),
    )->toBeFalse();
});

test('guests cannot destroy web push subscriptions', function () {
    $this->deleteJson(route('patient.push-subscriptions.destroy'))->assertUnauthorized();
});
