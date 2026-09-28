import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

window.psisAiChat = {
    prefix: 'psis-ai-chat:',
    key(userId) {
        return this.prefix + String(userId || 'anon');
    },
    load(userId) {
        try {
            const raw = sessionStorage.getItem(this.key(userId));
            const saved = raw ? JSON.parse(raw) : [];
            if (! Array.isArray(saved)) return [];

            return saved
                .filter((m) => m && (m.role === 'user' || m.role === 'assistant') && typeof m.text === 'string')
                .slice(-50);
        } catch (e) {
            return [];
        }
    },
    save(userId, messages) {
        try {
            const clean = (Array.isArray(messages) ? messages : [])
                .filter((m) => m && (m.role === 'user' || m.role === 'assistant') && typeof m.text === 'string')
                .slice(-50)
                .map((m) => ({ role: m.role, text: String(m.text) }));
            sessionStorage.setItem(this.key(userId), JSON.stringify(clean));
        } catch (e) {}
    },
    clear() {
        try {
            const keys = [];
            for (let i = 0; i < sessionStorage.length; i++) {
                const k = sessionStorage.key(i);
                if (k && k.startsWith(this.prefix)) keys.push(k);
            }
            keys.forEach((k) => sessionStorage.removeItem(k));
        } catch (e) {}
    },
};

if (document.querySelector('.psis-login-switch')) {
    window.psisAiChat.clear();
}

Alpine.start();
