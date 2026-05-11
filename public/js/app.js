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

        // Private user channel — receives call events wherever the callee is in the app,
        // not just when their specific chat is open.
        window.Echo.private(`user.${window.AUTH_USER.id}`)
            .notification((notification) => { handleNotification(notification); })
            .listen('.call.initiated', (data) => {
                // data: { call_id, room_id, type, caller: { id, name, avatar_url } }
                window.dispatchEvent(new CustomEvent('incoming-call', { detail: data }));
            });
    } catch(e) { console.warn('Echo setup failed:', e); }
}

// ── Call polling fallback (when Echo/Pusher not connected) ────────────────────
let _lastPendingCallId = null;
function startCallPolling() {
    if (!window.AUTH_USER) return;
    setInterval(async () => {
        if (echoReady) return;
        try {
            const res  = await fetch('/calls/pending', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
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
                // Normalise: accept both { call_id, ... } and { call: { call_id, ... } }
                const detail = (e.detail?.call_id != null) ? e.detail : e.detail?.call;
                if (!detail?.call_id) return;
                this.call = detail;
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

        stopRingtone() {
            if (this.audio) { this.audio.pause(); this.audio = null; }
        },

        answerCall() {
            this.stopRingtone();
            fetch(`/calls/${this.call.call_id}/answer`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            });
            window.open(`/calls/${this.call.call_id}/room`, '_blank', 'width=900,height=700');
            this.call = null;
        },

        rejectCall() {
            this.stopRingtone();
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

// ── Helpers ───────────────────────────────────────────────────────────────────
function markUserOnline(userId, isOnline) {
    document.querySelectorAll(`.online-indicator-${userId}`).forEach(el => {
        el.style.display = isOnline ? 'block' : 'none';
    });
}

function handleNotification(notification) {
    window.dispatchEvent(new CustomEvent('toast', {
        detail: { message: notification.data?.message || 'New notification', type: 'info' }
    }));
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

function setReply(message)       { window.dispatchEvent(new CustomEvent('set-reply',       { detail: message })); }
function editMessage(id, body)   { window.dispatchEvent(new CustomEvent('edit-message',    { detail: { id, body } })); }
function forwardMessage(id)      { window.dispatchEvent(new CustomEvent('forward-message', { detail: { id } })); }

function deleteMessage(messageId) {
    const type   = confirm('Delete for everyone?\n\nOK = for everyone, Cancel = for me only') ? 'for_everyone' : 'for_me';
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

// ── Voice note player ─────────────────────────────────────────────────────────
// Called by the play button in message.blade.php
function toggleVoicePlay(btn) {
    const player = btn.closest('.vn-player');
    const audio  = player?.querySelector('audio');
    if (!audio) return;

    const playIcon  = `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>`;
    const pauseIcon = `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>`;

    if (audio.paused) {
        // Pause every other voice note
        document.querySelectorAll('.vn-player audio').forEach(a => {
            if (a !== audio) {
                a.pause();
                a.closest('.vn-player')?.querySelector('.vn-btn')?.setAttribute('innerHTML', playIcon);
            }
        });
        audio.play().catch(() => {});
        btn.innerHTML = pauseIcon;
    } else {
        audio.pause();
        btn.innerHTML = playIcon;
    }

    audio.onended = () => {
        btn.innerHTML = playIcon;
        const fill = player.querySelector('.vn-progress-fill');
        const time = player.querySelector('.vn-time');
        if (fill) fill.style.width = '0%';
        if (time) time.textContent = player.querySelector('.vn-duration')?.textContent || '0:00';
    };

    audio.ontimeupdate = () => {
        if (!audio.duration) return;
        const pct  = (audio.currentTime / audio.duration) * 100;
        const fill = player.querySelector('.vn-progress-fill');
        const time = player.querySelector('.vn-time');
        if (fill) fill.style.width = pct + '%';
        if (time) {
            const s = Math.floor(audio.currentTime);
            time.textContent = `${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
        }
    };
}

// ── Theme management ──────────────────────────────────────────────────────────
function applyTheme(theme) {
    const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDark);
    document.getElementById('theme-color-meta')?.setAttribute('content', isDark ? '#1a1a2e' : '#6366f1');
    localStorage.setItem('voxchat_theme', theme);
}

const savedTheme = localStorage.getItem('voxchat_theme') || 'system';
applyTheme(savedTheme);

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (localStorage.getItem('voxchat_theme') === 'system') applyTheme('system');
});
