import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { useEcho } from '@/Composables/useEcho';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const { initializeEcho, disconnect } = useEcho();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Initialize Echo if user is already authenticated
        if (props.initialPage.props.auth.user) {
            window.Laravel = {
                user: props.initialPage.props.auth.user
            };
            initializeEcho();
        }

        // Listen for navigation to detect when user becomes authenticated
        router.on('success', (event) => {
            const newUser = event.detail?.page?.props.auth.user;
            const wasAuthenticated = window.Laravel?.user;

            if (newUser && !wasAuthenticated) {
                // User just logged in
                window.Laravel = {
                    user: newUser
                };
                initializeEcho();
            } else if (!newUser && wasAuthenticated) {
                // User just logged out
                delete window.Laravel;
                disconnect();
            } else if (newUser && wasAuthenticated) {
                // User was already authenticated, update their data
                window.Laravel.user = newUser;
            }
        });

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
