/**
 * MYBHEG Toast Notification System
 * Premium, glassmorphism-style notifications
 * Usage: showToast({ type: 'success', title: 'Başarılı', message: 'İşlem tamamlandı.' });
 */
(function() {
    'use strict';

    const ICONS = {
        success: 'bi-check-circle-fill',
        error: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };

    /**
     * Show a toast notification.
     * @param {Object} options - Toast options
     * @param {'success'|'error'|'warning'|'info'} options.type - Toast type
     * @param {string} options.title - Title text
     * @param {string} options.message - Body message
     * @param {number} [options.duration=4000] - Duration in ms before auto-dismiss
     */
    window.showToast = function({ type = 'info', title = '', message = '', duration = 4000 } = {}) {
        // Ensure container exists
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `mybheg-toast toast-${type}`;
        toast.style.setProperty('--toast-duration', `${duration}ms`);
        toast.innerHTML = `
            <i class="bi ${ICONS[type] || ICONS.info} toast-icon"></i>
            <div class="toast-body">
                ${title ? `<strong>${title}</strong>` : ''}
                ${message}
            </div>
            <button class="toast-close" aria-label="Kapat">&times;</button>
        `;

        // Set timer animation duration
        toast.style.cssText += `--toast-dur: ${duration}ms;`;
        const afterRule = toast.querySelector('.toast-close');
        
        // Apply timer animation inline for the ::after pseudo
        toast.style.animationDuration = '0.4s';
        
        // Close button handler
        afterRule.addEventListener('click', () => dismiss(toast));

        // Auto dismiss
        const timeoutId = setTimeout(() => dismiss(toast), duration);
        
        // Pause timer on hover
        toast.addEventListener('mouseenter', () => {
            clearTimeout(timeoutId);
            toast.style.animationPlayState = 'paused';
        });

        container.appendChild(toast);

        // Limit to 5 toasts max
        while (container.children.length > 5) {
            container.removeChild(container.firstChild);
        }
    };

    function dismiss(toast) {
        if (toast.classList.contains('hide')) return;
        toast.classList.add('hide');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
        // Fallback removal
        setTimeout(() => { if (toast.parentNode) toast.remove(); }, 500);
    }
})();
