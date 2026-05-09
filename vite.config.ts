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

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devServerHost =
        env.VITE_DEV_SERVER_HOST
        ?? hostnameFromAppUrl(env.APP_URL ?? '')
        ?? 'localhost';

    return {
        plugins: [
            tailwindcss(),
            laravel({
                input: ['resources/js/app.ts'],
                refresh: true,
                https: true
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
            hmr: {
                host: devServerHost,
            },
        },
        ssr: {
            noExternal: ['oh-vue-icons'],
        },
    };
});
