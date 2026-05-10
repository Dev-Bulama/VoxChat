/**
 * VoxChat — Main Application JavaScript
 * Real-time messaging, calling, and notifications
 */

// ── Service Worker Registration ──────────────────────────────────────────────
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}

// ── Laravel Echo / Pusher Setup ───────────────────────────────────────────────
let echoReady = false;
if (typeof Pusher !== 'undefined' && typeof Echo !== 'undefined' && window.AUTH_USER && window.PUSHER_KEY) {
    try {
        window.Echo = new Echo({
            broadcaster:    'pusher',
            key:            window.PUSHER_KEY,
            cluster:        window.PUSHER_CLUSTER,
            wsHost:         window.PUSHER_HOST || window.location.hostname,
            wsPort:         window.PUSHER_PORT || 6001,
            wssPort:        window.PUSHER_PORT || 6001,
            forceTLS:       window.location.protocol === 'https:',
            encrypted:      true,
            enabledTransports: ['ws', 'wss'],
            authEndpoint:   '/broadcasting/auth',
        });

        window.Echo.connector.pusher.connection.bind('connected', () => {
            echoReady = true;
            window.dispatchEvent(new CustomEvent('echo-connected'));
        });

        // Global user presence
        window.Echo.join('presence.global')
            .here((users) => { window.dispatchEvent(new CustomEvent('presence-here', { detail: users })); })
            .joining((user) => { markUserOnline(user.id, true); })
            .leaving((user) => { markUserOnline(user.id, false); });

        // Private user channel for personal notifications + calls
        window.Echo.private(`user.${window.AUTH_USER.id}`)
            .notification((notification) => { handleNotification(notification); })
            .listen('.call.initiated', (data) => {
                window.dispatchEvent(new CustomEvent('incoming-call', { detail: data }));
            });
    } catch(e) { console.warn('Echo setup failed:', e); }
}

// ── Call polling fallback (when Echo/Pusher not connected) ────────────────────
let _lastPendingCallId = null;
function startCallPolling() {
    if (!window.AUTH_USER) return;
    setInterval(async () => {
        if (echoReady) return; // Echo handles it
        try {
            const res  = await fetch('/calls/pending', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.call && data.call.call_id !== _lastPendingCallId) {
                _lastPendingCallId = data.call.call_id;
                window.dispatchEvent(new CustomEvent('incoming-call', { detail: data.call }));
            } else if (!data.call) {
                _lastPendingCallId = null;
            }
        } catch {}
    }, 5000);
}
document.addEventListener('DOMContentLoaded', startCallPolling);

// ── Incoming Call Manager (Alpine component) ──────────────────────────────────
document.addEventListener('alpine:init', () => {
    Alpine.data('incomingCallManager', () => ({
        call: null,
        audio: null,

        init() {
            window.addEventListener('incoming-call', (e) => {
                this.call = e.detail;
                this.playRingtone();
            });
        },

        playRingtone() {
            try {
                this.audio = new Audio('/audio/ringtone.mp3');
                this.audio.loop = true;
                this.audio.play().catch(() => {});
            } catch {}
        },

        answerCall() {
            if (this.audio) { this.audio.pause(); this.audio = null; }
            window.open(`/calls/${this.call.call_id}/room`, '_blank', 'width=900,height=700');
            this.call = null;
        },

        rejectCall() {
            if (this.audio) { this.audio.pause(); this.audio = null; }
            fetch(`/calls/${this.call.call_id}/reject`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            });
            this.call = null;
        },
    }));

    // Toast notifications manager
    Alpine.data('toastManager', () => ({
        toasts: [],
        counter: 0,

        show({ message, type = 'info', duration = 3000 }) {
            const id = ++this.counter;
            this.toasts.push({ id, message, type, visible: true });
            setTimeout(() => {
                const toast = this.toasts.find(t => t.id === id);
                if (toast) toast.visible = false;
                setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300);
            }, duration);
        },
    }));
});

// ── Helper functions ──────────────────────────────────────────────────────────
function markUserOnline(userId, isOnline) {
    document.querySelectorAll(`.online-indicator-${userId}`).forEach(el => {
        el.style.display = isOnline ? 'block' : 'none';
    });
}

function handleNotification(notification) {
    // Show toast
    window.dispatchEvent(new CustomEvent('toast', {
        detail: { message: notification.data?.message || 'New notification', type: 'info' }
    }));

    // Update badge
    const badge = document.getElementById('notification-badge');
    if (badge) {
        const count = parseInt(badge.textContent || '0') + 1;
        badge.textContent = count > 9 ? '9+' : count;
        badge.style.display = 'flex';
    }
}

function reactToMessage(messageId, emoji) {
    const chatId = window.location.pathname.split('/')[2];
    fetch(`/chats/${chatId}/messages/${messageId}/react`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ emoji }),
    }).catch(console.error);
}

function setReply(message) {
    window.dispatchEvent(new CustomEvent('set-reply', { detail: message }));
}

function editMessage(messageId, body) {
    window.dispatchEvent(new CustomEvent('edit-message', { detail: { id: messageId, body } }));
}

function forwardMessage(messageId) {
    window.dispatchEvent(new CustomEvent('forward-message', { detail: { id: messageId } }));
}

function deleteMessage(messageId) {
    const type = confirm('Delete for everyone?\n\nOK = for everyone, Cancel = for me only') ? 'for_everyone' : 'for_me';
    const chatId = window.location.pathname.split('/')[2];
    fetch(`/chats/${chatId}/messages/${messageId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ type }),
    }).then(() => {
        const el = document.getElementById(`msg-${messageId}`);
        if (el) el.innerHTML = '<div class="flex justify-center"><span class="text-xs text-gray-400 italic py-1">Message deleted</span></div>';
    }).catch(console.error);
}

// ── Theme management ──────────────────────────────────────────────────────────
function applyTheme(theme) {
    const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDark);
    document.getElementById('theme-color-meta')?.setAttribute('content', isDark ? '#1a1a2e' : '#6366f1');
    localStorage.setItem('voxchat_theme', theme);
}

// Auto-apply saved theme
const savedTheme = localStorage.getItem('voxchat_theme') || 'system';
applyTheme(savedTheme);

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (localStorage.getItem('voxchat_theme') === 'system') applyTheme('system');
});
