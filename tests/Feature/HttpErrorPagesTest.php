<?php

use Illuminate\Foundation\Exceptions\RegisterErrorViewPaths;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

beforeEach(function (): void {
    (new RegisterErrorViewPaths)();
});

test('429 error page uses medibeheer styling and dutch copy', function () {
    $html = view('errors::429', [
        'exception' => new TooManyRequestsHttpException(45, 'Too Many Requests'),
    ])->render();

    expect($html)
        ->toContain('bg-bg')
        ->toContain('Te veel verzoeken')
        ->toContain('Terug naar start')
        ->toContain('favicon.svg')
        ->not->toContain('bg-white text-black');
});

test('429 error page shows throttle countdown message when retry-after is set', function () {
    $html = view('errors::429', [
        'exception' => new TooManyRequestsHttpException(12, 'Too Many Requests'),
    ])->render();

    expect($html)
        ->toContain('Te veel verzoeken. Probeer het opnieuw over 12 seconden')
        ->toContain('let remainingSeconds = 12');
});

test('404 error page uses medibeheer styling and dutch copy', function () {
    $html = view('errors::404', [
        'exception' => new NotFoundHttpException,
    ])->render();

    expect($html)
        ->toContain('Pagina niet gevonden')
        ->toContain('bg-bg')
        ->not->toContain('Not Found');
});
