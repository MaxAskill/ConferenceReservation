import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
        for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
          app.component(key, component)
        }
        app
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(ElementPlus)
            .mount(el);

        return app;
    },
    progress: {
        color: '#4B5563',
    },
});
