/**
 * Toggle de tema claro/oscuro.
 * Se aplica la clase "dark" al <html> y se persiste en localStorage.
 */
(function () {
    const STORAGE_KEY = 'cada_theme';

    function apply(theme) {
        const root = document.documentElement;
        if (theme === 'dark') {
            root.classList.add('dark');
        } else {
            root.classList.remove('dark');
        }
    }

    function current() {
        return localStorage.getItem(STORAGE_KEY) || 'light';
    }

    // Inicializa lo más pronto posible (evita FOUC)
    apply(current());

    window.CADATheme = {
        toggle() {
            const next = current() === 'dark' ? 'light' : 'dark';
            localStorage.setItem(STORAGE_KEY, next);
            apply(next);
            return next;
        },
        set(theme) {
            localStorage.setItem(STORAGE_KEY, theme);
            apply(theme);
        },
        current
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                window.CADATheme.toggle();
            });
        });
    });
})();
