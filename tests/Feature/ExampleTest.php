<?php

declare(strict_types=1);
use Illuminate\Foundation\Testing\TestCase;

test('homepage redirects to login when not authenticated', function () {
    /** @var TestCase $this */
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
