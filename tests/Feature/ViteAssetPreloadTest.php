<?php

test('guest pages do not duplicate vite css preloads in link headers', function () {
    $hotFile = public_path('hot');
    $hotBackup = public_path('hot.vite-asset-preload-test');

    if (file_exists($hotFile)) {
        rename($hotFile, $hotBackup);
    }

    try {
        $response = $this->get(route('login'));

        $response->assertSuccessful();

        expect($response->headers->get('Link'))->toBeNull();
        expect($response->getContent())->toContain('rel="stylesheet"');
    } finally {
        if (file_exists($hotBackup)) {
            rename($hotBackup, $hotFile);
        }
    }
});
