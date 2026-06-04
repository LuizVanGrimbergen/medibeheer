let configured = false;

export async function ensureLaravelEchoIsConfigured(): Promise<void> {
    if (configured || globalThis.window === undefined) {
        return;
    }

    const { configureEcho } = await import('@laravel/echo-vue');

    configureEcho({
        broadcaster: 'pusher',
    });

    configured = true;
}
