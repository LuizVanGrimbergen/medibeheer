<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\TestCase;

test('homepage shows the public landing page when not authenticated', function () {
    /** @var TestCase $this */
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Guest/Home'));
});

test('homepage ships a minimal ziggy route payload', function () {
    /** @var TestCase $this */
    $response = $this->get('/');

    $response->assertOk();

    $content = $response->getContent();
    expect($content)->toBeString()
        ->toContain('id="ziggy-routes-json"')
        ->toContain('"home"')
        ->not->toContain('patient.dashboard');
});
