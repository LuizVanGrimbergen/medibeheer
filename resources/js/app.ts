import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { i18n } from '@/i18n';
import {
    listenForMedicationPushServiceWorkerUpdates,
    registerMedicationPushServiceWorker,
} from '@/lib/medicationPushServiceWorker';
import { route as ziggyRoute } from 'ziggy-js';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

if (globalThis.window !== undefined) {
    globalThis.route = ziggyRoute;
}

function shouldRegisterMedicationPushServiceWorker(pageProps: unknown): boolean {
    if (typeof pageProps !== 'object' || pageProps === null) {
        return false;
    }

    const auth = (pageProps as { auth?: { user?: { role?: string } } }).auth;

    return auth?.user?.role === 'patient';
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pageComponents = import.meta.glob<DefineComponent>('./pages/**/*.vue');

const bootstrapApp = () => {
    createInertiaApp({
        title: (title) =>
            title !== '' && title !== null && title !== undefined
                ? `${title} - ${appName}`
                : appName,
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

            if (shouldRegisterMedicationPushServiceWorker(props.initialPage.props)) {
                listenForMedicationPushServiceWorkerUpdates();
                void registerMedicationPushServiceWorker();
            }

            if (el !== null) {
                vueApp.mount(el);
            }

            return vueApp;
        },
        progress: false,
    });
};

if (globalThis.window !== undefined) {
    bootstrapApp();
}
