<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('password can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->from('/settings')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'Jk8@vN5#qR3!zT1$',
            'password_confirmation' => 'Jk8@vN5#qR3!zT1$',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings');

    $this->assertTrue(Hash::check('Jk8@vN5#qR3!zT1$', $user->refresh()->password));
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->from('/settings')
        ->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'Jk8@vN5#qR3!zT1$',
            'password_confirmation' => 'Jk8@vN5#qR3!zT1$',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect('/settings');
});

test('password update is rate limited after too many attempts', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $_) {
        $this
            ->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->from('/settings')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'Jk8@vN5#qR3!zT1$',
                'password_confirmation' => 'Jk8@vN5#qR3!zT1$',
            ]);
    }

    $response = $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->from('/settings')
        ->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'Jk8@vN5#qR3!zT1$',
            'password_confirmation' => 'Jk8@vN5#qR3!zT1$',
        ]);

    $response->assertStatus(429);
});
