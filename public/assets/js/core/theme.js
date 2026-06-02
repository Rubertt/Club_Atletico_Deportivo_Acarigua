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

    // Inyectar icono de ayuda (?) en labels con data-tooltip de forma limpia
    function injectTooltipIcons() {
        document.querySelectorAll('label[data-tooltip]').forEach(label => {
            // Evitar duplicados si ya tiene el icono inyectado
            if (label.querySelector('.label-tooltip-icon')) return;

            // Envolver el contenido existente en un span seguro para el maquetado flexbox
            const wrapper = document.createElement('span');
            wrapper.className = 'label-text-wrapper';
            while (label.firstChild) {
                wrapper.appendChild(label.firstChild);
            }
            label.appendChild(wrapper);

            // Crear y añadir el icono de ayuda (?) con Phosphor Icons
            const iconSpan = document.createElement('span');
            iconSpan.className = 'label-tooltip-icon';
            iconSpan.innerHTML = '<i class="ph ph-question"></i>';
            label.appendChild(iconSpan);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Ejecución inicial al cargar la página
        injectTooltipIcons();

        // Observador de mutaciones para inyectar iconos dinámicamente en modales, pestañas o formularios AJAX
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver((mutations) => {
                let hasNewLabels = false;
                mutations.forEach(mutation => {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            if (node.tagName === 'LABEL' && node.hasAttribute('data-tooltip')) {
                                hasNewLabels = true;
                            } else if (node.querySelector && node.querySelector('label[data-tooltip]')) {
                                hasNewLabels = true;
                            }
                        }
                    });
                });
                if (hasNewLabels) {
                    injectTooltipIcons();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }

        // Toggle de tema claro/oscuro
        document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                window.CADATheme.toggle();
            });
        });
    });
})();
