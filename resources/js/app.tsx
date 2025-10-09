import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from './hooks/use-appearance';
import { configureEcho } from '@laravel/echo-react';
import { MfaProvider} from '@/MfaProvider';
import { FingerprintProvider } from './components/FingerprintProvider';
import {APIProvider} from '@vis.gl/react-google-maps';

configureEcho({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true, // Optional: disable stats for local development

    activityTimeout: 3000,
    pongTimeout: 3000,
    unavailableTimeout: 1000,
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const getCsrfToken = (): string | null => {
    // From meta tag (if using Laravel-style CSRF)
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }

    // Or from your React app's config/context
    return null;
};

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <MfaProvider>
                <FingerprintProvider endpoint="/api/fingerprint" csrfToken={getCsrfToken()} autoCollectOnMount={true}>
                    <APIProvider apiKey={'AIzaSyDX0I9nlNK5SfmzWLDU0vnqr83Aj8HTqnY'} onLoad={() => console.log('Maps API has loaded.')}>
                        <App {...props} />
                    </APIProvider>
                </FingerprintProvider>
            </MfaProvider>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
