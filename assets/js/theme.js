/**
 * MYBHEG Theme Manager
 * Dark/Light mode toggle with localStorage persistence
 */
(function() {
    'use strict';

    const STORAGE_KEY = 'mybheg_theme';

    function getPreferred() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) return saved;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function apply(theme) {
        document.documentElement.classList.toggle('dark-mode', theme === 'dark');
        localStorage.setItem(STORAGE_KEY, theme);

        // Update toggle button icon if it exists
        const btn = document.getElementById('themeToggleBtn');
        if (btn) {
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            }
        }

        // Update meta theme-color
        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) {
            meta.content = theme === 'dark' ? '#020617' : '#1E293B';
        }
    }

    // Apply immediately to prevent FOUC
    apply(getPreferred());

    // Expose toggle function globally
    window.toggleTheme = function() {
        const current = document.documentElement.classList.contains('dark-mode') ? 'dark' : 'light';
        apply(current === 'dark' ? 'light' : 'dark');
    };
})();
