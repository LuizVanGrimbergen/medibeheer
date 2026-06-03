<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\TestCase;

test('homepage shows the public landing page when not authenticated', function () {
    /** @var TestCase $this */
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Guest/Home'));
});
