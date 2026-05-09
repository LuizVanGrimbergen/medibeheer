<?php

use App\Models\User;

test('verified family members can visit family overview', function () {
    $user = User::factory()->familyMember()->create();

    $response = $this->actingAs($user)->get(route('family.overview'));

    $response->assertOk();
});

test('verified family members can visit family updates', function () {
    $user = User::factory()->familyMember()->create();

    $response = $this->actingAs($user)->get(route('family.updates'));

    $response->assertOk();
});

test('verified family members can visit family wellbeing', function () {
    $user = User::factory()->familyMember()->create();

    $response = $this->actingAs($user)->get(route('family.wellbeing'));

    $response->assertOk();
});

test('patients cannot visit family overview', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->get(route('family.overview'));

    $response->assertForbidden();
});

test('patients cannot visit family updates', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->get(route('family.updates'));

    $response->assertForbidden();
});

test('patients cannot visit family wellbeing', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->get(route('family.wellbeing'));

    $response->assertForbidden();
});

test('doctors cannot visit family overview', function () {
    $user = User::factory()->create(['role' => 'doctor']);

    $response = $this->actingAs($user)->get(route('family.overview'));

    $response->assertForbidden();
});

test('guests are redirected when visiting family overview', function () {
    $response = $this->get(route('family.overview'));

    $response->assertRedirect(route('login'));
});

test('guests are redirected when visiting family updates', function () {
    $response = $this->get(route('family.updates'));

    $response->assertRedirect(route('login'));
});

test('guests are redirected when visiting family wellbeing', function () {
    $response = $this->get(route('family.wellbeing'));

    $response->assertRedirect(route('login'));
});
