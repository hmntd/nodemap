import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { DefaultApolloClient } from '@vue/apollo-composable';
import { apolloClient } from '@/lib/apollo';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'nodemap';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .provide(DefaultApolloClient, apolloClient)
            .mount(el);
    },
    layout: (name) => {
        switch (true) {
            case name === 'Welcome' || name === 'DiagramEditor' || name === 'WorkspaceManagement':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#38bdf8',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
