import inertia from '@inertiajs/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig, loadEnv } from 'vite';
import tailwindcss from '@tailwindcss/vite';


function hostnameFromAppUrl(appUrl: string): string | null {
    try {
        return new URL(appUrl).hostname || null;
    } catch {
        return null;
    }
}

function appUrlUsesHttps(appUrl: string): boolean {
    try {
        return new URL(appUrl).protocol === 'https:';
    } catch {
        return false;
    }
}

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const appUrlFromEnv = env.APP_URL ?? '';
    const appHostname = hostnameFromAppUrl(appUrlFromEnv);
    const appUsesHttps = appUrlUsesHttps(appUrlFromEnv);
    const devServerHost =
        env.VITE_DEV_SERVER_HOST ?? appHostname ?? 'localhost';
    const appOrigin = appUrlFromEnv || `http://${devServerHost}:8000`;
    const devProtocol = appUsesHttps ? 'https' : 'http';
    const devServerOrigin = `${devProtocol}://${devServerHost}:5173`;

    let detectTls: string | boolean;
    if (! appUsesHttps) {
        detectTls = false;
    } else if (
        appHostname !== null
        && appHostname !== ''
        && appHostname !== 'localhost'
    ) {
        detectTls = appHostname;
    } else {
        detectTls = true;
    }

    return {
        plugins: [
            tailwindcss(),
            laravel({
                input: ['resources/js/app.ts'],
                refresh: true,
                detectTls,
            }),
            inertia(),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        server: {
            host: true,
            port: 5173,
            strictPort: true,
            ...(appUsesHttps ? {} : { https: false }),
            origin: devServerOrigin,
            cors: {
                origin: appOrigin,
            },
            hmr: {
                host: devServerHost,
                protocol: appUsesHttps ? 'wss' : 'ws',
            },
        },
        ssr: {
            noExternal: ['oh-vue-icons'],
        },
    };
});
