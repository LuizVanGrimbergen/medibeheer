import inertia from '@inertiajs/vite';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig, loadEnv } from 'vite';

const projectRoot = path.dirname(fileURLToPath(import.meta.url));

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
    if (!appUsesHttps) {
        detectTls = false;
    } else if (
        appHostname !== null &&
        appHostname !== '' &&
        appHostname !== 'localhost'
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
        resolve: {
            alias: {
                'ziggy-js': path.resolve(projectRoot, 'vendor/tightenco/ziggy'),
            },
            dedupe: ['@vueuse/core', '@vueuse/shared'],
        },
        build: {
            rolldownOptions: {
                output: {
                    manualChunks(id) {
                        if (id.includes('node_modules/gsap')) {
                            return 'vendor-gsap';
                        }

                        if (id.includes('node_modules/vue-i18n')) {
                            return 'vendor-i18n';
                        }

                        if (
                            id.includes('node_modules/lucide-vue-next') ||
                            id.includes('node_modules/@lucide/')
                        ) {
                            return 'vendor-icons';
                        }

                        if (id.includes('node_modules/reka-ui')) {
                            return 'vendor-reka-ui';
                        }
                    },
                },
            },
        },
        ssr: {
            noExternal: ['oh-vue-icons', 'ziggy-js'],
        },
    };
});
