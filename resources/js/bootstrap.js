import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? '127.0.0.1',
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: false,
    enabledTransports: ['ws'],
});

// DEBUG: log every raw message from Reverb
window.Echo.connector.pusher.connection.bind('message', (msg) => {
    console.log('[Reverb RAW]', msg);
});
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('[Reverb] connected');
});
window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('[Reverb] connection error', err);
});
