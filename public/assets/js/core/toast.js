/**
 * CadaToast — Sistema de notificaciones flotantes para CADA
 * ─────────────────────────────────────────────────────────
 * Uso:
 *   CadaToast.success('Operación completada.', () => window.location.reload());
 *   CadaToast.success('Cambios guardados.');   // sin navegación
 *
 * El toast se inserta en la parte superior de .admin-content (o del body
 * si no existe), con una barra de progreso que se consume en 5 segundos.
 * Si se provee onDone, se ejecuta automáticamente a los 1500 ms.
 */
(function () {
    'use strict';

    // ─── Constantes ────────────────────────────────────────────────────────
    const LIFETIME_MS   = 5000;   // Tiempo total de vida del toast
    const NAVIGATE_MS   = 1500;   // Tiempo antes de ejecutar onDone (navigate/reload)
    const TRANSITION_MS = 300;    // Duración de la animación de salida

    // ─── Estado interno ────────────────────────────────────────────────────
    let currentToast   = null;
    let navigateTimer  = null;
    let dismissTimer   = null;

    // ─── Helpers ───────────────────────────────────────────────────────────
    function getContainer() {
        return document.querySelector('.admin-content') || document.body;
    }

    function clearTimers() {
        clearTimeout(navigateTimer);
        clearTimeout(dismissTimer);
        navigateTimer = null;
        dismissTimer  = null;
    }

    function removeToast(toast) {
        if (!toast || !toast.parentNode) return;
        toast.classList.add('cada-toast--leaving');
        setTimeout(() => {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
            if (currentToast === toast) currentToast = null;
        }, TRANSITION_MS);
    }

    // ─── Función principal ─────────────────────────────────────────────────
    function show(message, type, onDone) {
        // Si ya hay un toast activo, lo descartamos antes
        if (currentToast) {
            clearTimers();
            removeToast(currentToast);
        }

        // ── Construir el elemento ──
        const toast = document.createElement('div');
        toast.className = `cada-toast cada-toast--${type}`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'polite');

        const icons = {
            success : 'ph-check-circle',
            error   : 'ph-x-circle',
            warning : 'ph-warning',
            info    : 'ph-info',
        };
        const icon = icons[type] || 'ph-info';

        toast.innerHTML = `
            <div class="cada-toast__progress"></div>
            <div class="cada-toast__body">
                <span class="cada-toast__icon"><i class="ph ${icon}"></i></span>
                <span class="cada-toast__message">${message}</span>
                <button class="cada-toast__close" aria-label="Cerrar notificación" type="button">
                    <i class="ph ph-x"></i>
                </button>
            </div>
        `;

        // ── Insertar al inicio del contenedor ──
        const container = getContainer();
        container.insertAdjacentElement('afterbegin', toast);
        currentToast = toast;

        // ── Animación de entrada (forzar reflow antes de añadir clase) ──
        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('cada-toast--visible'));
        });

        // ── Cierre manual ──
        toast.querySelector('.cada-toast__close').addEventListener('click', () => {
            clearTimers();
            removeToast(toast);
        });

        // ── Timer: ejecutar onDone a los 1500 ms ──
        if (typeof onDone === 'function') {
            navigateTimer = setTimeout(() => {
                onDone();
            }, NAVIGATE_MS);
        }

        // ── Timer: auto-remover el toast a los 5000 ms ──
        dismissTimer = setTimeout(() => {
            removeToast(toast);
        }, LIFETIME_MS);
    }

    // ─── API Pública ───────────────────────────────────────────────────────
    window.CadaToast = {
        /**
         * Muestra un toast de éxito verde.
         * @param {string}    message  Texto a mostrar
         * @param {Function}  [onDone] Callback ejecutado a los 1.5 s (redirect/reload)
         */
        success(message, onDone) {
            show(message, 'success', onDone);
        },

        /**
         * Descarta el toast activo manualmente.
         */
        dismiss() {
            clearTimers();
            removeToast(currentToast);
        }
    };

    // ─── Mantener compatibilidad con el Toast anterior ────────────────────
    window.Toast = {
        show(message, type = 'info') {
            show(message, type, null);
        }
    };

})();
