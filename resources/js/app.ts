import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { i18n } from '@/i18n';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pageComponents = import.meta.glob<DefineComponent>('./pages/**/*.vue');

const bootstrapApp = () => {
    createInertiaApp({
        title: (title) => `${title} - ${appName}`,
        resolve: async (name) => {
            const page = (await resolvePageComponent(
                `./pages/${name}.vue`,
                pageComponents,
            )) as DefineComponent | { default: DefineComponent };

            return 'default' in page ? page.default : page;
        },
        setup({ el, App, props, plugin }) {
            const vueApp = createApp({ render: () => h(App, props) });

            vueApp.use(plugin);
            vueApp.use(ZiggyVue);
            vueApp.use(i18n);

            if (el !== null) {
                vueApp.mount(el);
            }

            return vueApp;
        },
        progress: {
            color: '#4B5563',
        },
    });
};

if (globalThis.window !== undefined) {
    bootstrapApp();
}
