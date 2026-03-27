import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST ?? window.location.hostname;
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT ?? 8080);
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

if (false && reverbKey) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}

const authUserId = Number(document.querySelector('meta[name="user-id"]')?.getAttribute('content') || 0);
if (!window.Laravel) {
    window.Laravel = {};
}
if (authUserId > 0) {
    window.Laravel.userId = authUserId;
}

if (!window.onlineUsersMap) {
    window.onlineUsersMap = {};
}

window.addOnline = function addOnline(user) {
    if (!user?.id) return;
    window.onlineUsersMap = {
        ...window.onlineUsersMap,
        [Number(user.id)]: 'online',
    };
    window.dispatchEvent(new CustomEvent('chat-presence-updated', { detail: window.onlineUsersMap }));
};

window.removeOnline = function removeOnline(user) {
    if (!user?.id) return;
    const nextMap = { ...window.onlineUsersMap };
    delete nextMap[Number(user.id)];
    window.onlineUsersMap = nextMap;
    window.dispatchEvent(new CustomEvent('chat-presence-updated', { detail: window.onlineUsersMap }));
};

if (window.Echo && window.Laravel?.userId) {
    window.Echo.join('chat')
        .here((users) => {
            window.axios.post('/api/chat/presence/status', { status: 'online' }).catch(() => {});
            const next = {};
            users.forEach((user) => {
                next[Number(user.id)] = 'online';
            });
            window.onlineUsersMap = next;
            window.dispatchEvent(new CustomEvent('chat-presence-updated', { detail: window.onlineUsersMap }));
        })
        .joining((user) => {
            window.addOnline(user);
        })
        .leaving((user) => {
            window.removeOnline(user);
        });
}

window.addEventListener('beforeunload', () => {
    if (!window.Laravel?.userId) return;
    try {
        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify({ status: 'offline' })], { type: 'application/json' });
            navigator.sendBeacon('/api/chat/presence/status', blob);
            return;
        }
        window.axios.post('/api/chat/presence/status', { status: 'offline' }).catch(() => {});
    } catch (error) {
        // no-op
    }
});
