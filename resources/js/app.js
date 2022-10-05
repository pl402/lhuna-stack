require('./bootstrap');

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import FontAwesomeIcon from "@/Components/fontawesome-icons";
import Notifications from 'notiwind';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Entrega Recepción';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => require(`./Pages/${name}.vue`),
    setup({ el, app, props, plugin }) {
        return createApp({ render: () => h(app, props) })
            .component("font-awesome-icon", FontAwesomeIcon)
            .use(plugin)
            .use(Notifications)
            .mixin({ methods: { route } })
            .mount(el);
    },
});

InertiaProgress.init({ color: '#4B5563' });
