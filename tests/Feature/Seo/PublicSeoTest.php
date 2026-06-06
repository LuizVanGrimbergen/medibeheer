<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase;

test('robots.txt disallows private app areas and references the sitemap', function () {
    /** @var TestCase $this */
    $response = $this->get('/robots.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    $content = $response->getContent();

    expect($content)
        ->toContain('User-agent: *')
        ->toContain('Disallow: /patient/')
        ->toContain('Disallow: /family/')
        ->toContain('Disallow: /doctor/')
        ->toContain('Sitemap: '.route('seo.sitemap', absolute: true));
});

test('sitemap lists public marketing and auth pages', function () {
    /** @var TestCase $this */
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $content = $response->getContent();

    expect($content)
        ->toContain(route('home', absolute: true))
        ->toContain(route('login', absolute: true))
        ->toContain(route('register', absolute: true))
        ->toContain(route('legal.privacy', absolute: true))
        ->toContain(route('legal.cookies', absolute: true));
});

test('homepage is indexable for guests and renders the public landing page', function () {
    /** @var TestCase $this */
    $response = $this->get('/');

    $response->assertOk();
    $response->assertHeaderMissing('X-Robots-Tag');
    $response->assertInertia(fn ($page) => $page->component('Guest/Home'));
});

test('homepage includes json-ld structured data in the initial html', function () {
    /** @var TestCase $this */
    $this->get('/')
        ->assertOk()
        ->assertSee('application/ld+json', false)
        ->assertSee('WebSite', false)
        ->assertSee('WebApplication', false)
        ->assertSee(route('home', absolute: true), false);
});

test('homepage includes a server-rendered meta description for search engines', function () {
    /** @var TestCase $this */
    $description = config('seo.pages.home.description');

    $this->get('/')
        ->assertOk()
        ->assertSee('<meta name="description" content="'.e($description).'">', false);
});

test('sitemap entries include changefreq and priority', function () {
    /** @var TestCase $this */
    $content = $this->get('/sitemap.xml')->assertOk()->getContent();

    expect($content)
        ->toContain('<changefreq>weekly</changefreq>')
        ->toContain('<priority>1.0</priority>');
});

test('login page uses a clean canonical url without query parameters', function () {
    /** @var TestCase $this */
    $this->get(route('login', ['role' => 'invalid']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('seo.canonicalUrl', route('login', absolute: true)));
});

test('authenticated users are redirected from the homepage to their dashboard', function () {
    /** @var TestCase $this */
    $patient = User::factory()->patient()->create();

    $this->actingAs($patient)
        ->get('/')
        ->assertRedirect(route('patient.dashboard'));
});

test('patient dashboard responses are not indexable', function () {
    /** @var TestCase $this */
    $patient = User::factory()->patient()->create();

    $this->actingAs($patient)
        ->get(route('patient.dashboard'))
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

test('patient family page responses are not indexable', function () {
    /** @var TestCase $this */
    $patient = User::factory()->patient()->create();

    $this->actingAs($patient)
        ->get(route('patient.family'))
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});
