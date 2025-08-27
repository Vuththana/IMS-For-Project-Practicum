// resources/js/app.tsx
import './bootstrap';
import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import ReactDOM from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import axios from 'axios'; // Ensure axios is imported

// Configure Axios for Laravel Sanctum (SPA Authentication)
axios.defaults.withCredentials = true; // Essential for sending session cookies
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'; // Laravel expects this header for AJAX requests

const queryClient = new QueryClient();

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.tsx');
        const path = `./Pages/${name}.tsx`;
        if (pages[path]) {
            return pages[path]();
        }
        console.error(`Inertia Page not found: ${path}`);
        return null;
    },
    setup({ el, App, props }) {
        ReactDOM.createRoot(el).render(
            <QueryClientProvider client={queryClient}>
                <App {...props} />
            </QueryClientProvider>
        );
    },
});