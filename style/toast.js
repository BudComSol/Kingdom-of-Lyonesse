/**
 * Kingdom of Lyonesse - Animated RPG Toast Notification Engine
 */
class ToastManager {
    constructor() {
        this.container = document.getElementById('toast-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
        this.initDOMAlerts();
    }

    show(message, type = 'success', duration = 3800) {
        if (!message) return;
        const toast = document.createElement('div');
        toast.className = `rpg-toast toast-${type}`;

        const icons = {
            success: '✨',
            error: '⚠️',
            warning: '⚡',
            info: '📜'
        };

        toast.innerHTML = `
            <div class="toast-icon">${icons[type] || '✨'}</div>
            <div class="toast-content">${message}</div>
            <button class="toast-close" type="button" aria-label="Close">&times;</button>
        `;

        const closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                toast.classList.add('toast-exit');
                setTimeout(() => toast.remove(), 350);
            });
        }

        this.container.appendChild(toast);

        // Sound effect integration
        if (window.gameAudio) {
            window.gameAudio.playClick();
        }

        // Auto remove
        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.add('toast-exit');
                setTimeout(() => toast.remove(), 350);
            }
        }, duration);
    }

    initDOMAlerts() {
        // Automatically convert any legacy server alert banners into toasts
        const notices = document.querySelectorAll('.notify-inner, .b-notice:not(.trsmgr)');
        notices.forEach(n => {
            const txt = n.innerText.trim();
            if (txt && !txt.includes('All arena combatants')) {
                const isError = n.classList.contains('red') || txt.toLowerCase().includes('error') || txt.toLowerCase().includes('invalid');
                this.show(txt, isError ? 'error' : 'success');
            }
        });
    }
}

// Global exposure
document.addEventListener('DOMContentLoaded', () => {
    window.toastManager = new ToastManager();
    window.showToast = (msg, type, dur) => window.toastManager.show(msg, type, dur);
});
